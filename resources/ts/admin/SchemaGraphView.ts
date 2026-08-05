import ForceGraph3D, { type ForceGraph3DInstance } from '3d-force-graph';
import { CanvasTexture, LinearFilter, Sprite, SpriteMaterial } from 'three';

/**
 * A single column of a table, as read from the live schema.
 */
interface SchemaColumn {
    name: string;
    type: string;
    nullable: boolean;
    primary: boolean;
    foreign: boolean;
}

/**
 * A table. The optional coordinates are written by the force layout.
 */
interface SchemaNode {
    id: string;
    domain: string;
    system: boolean;
    rows: number;
    columnCount: number;
    columns: SchemaColumn[];
    x?: number;
    y?: number;
    z?: number;
    vx?: number;
    vy?: number;
    vz?: number;
}

/**
 * A foreign key constraint. Source and target arrive as table names and
 * are replaced by node objects once the layout has resolved them.
 */
interface SchemaLink {
    source: string | SchemaNode;
    target: string | SchemaNode;
    columns: string[];
    references: string[];
    onDelete: string;
}

/**
 * Presentation metadata for one domain.
 */
interface SchemaDomain {
    label: string;
    color: string;
    pill: string;
}

/**
 * The payload rendered into the page by the Filament page.
 */
interface SchemaPayload {
    nodes: SchemaNode[];
    links: SchemaLink[];
    domains: Record<string, SchemaDomain>;
}

/**
 * Interactive 3D explorer for the database schema.
 *
 * Tables are spheres sized by row count and coloured by domain, foreign
 * keys are directed edges, and hovering a table dims everything it is
 * not related to. Selecting a table fills the side panel with its
 * columns and both directions of its relationships.
 */
export class SchemaGraphView {
    private readonly root: HTMLElement;

    private readonly stage: HTMLElement;

    private readonly panel: HTMLElement;

    private readonly status: HTMLElement | null;

    private readonly payload: SchemaPayload;

    private readonly graph: ForceGraph3DInstance<SchemaNode, SchemaLink>;

    private readonly labels = new Map<string, Sprite>();

    /**
     * Domains the viewer has switched off through the legend.
     */
    private readonly hiddenDomains = new Set<string>();

    /**
     * Tables and edges connected to the hovered or selected table.
     */
    private related = { nodes: new Set<string>(), links: new Set<SchemaLink>() };

    private selected: SchemaNode | null = null;

    private showSystem = false;

    private readonly reducedMotion: boolean;

    private resizeObserver: ResizeObserver | null = null;

    /**
     * Builds the graph inside an already rendered page container.
     *
     * @param root - The element carrying the payload and the controls.
     */
    constructor(root: HTMLElement) {
        this.root = root;
        this.stage = root.querySelector<HTMLElement>('[data-dbg-stage]') as HTMLElement;
        this.panel = root.querySelector<HTMLElement>('[data-dbg-panel-body]') as HTMLElement;
        this.status = root.querySelector<HTMLElement>('[data-dbg-status]');
        this.payload = this.readPayload();
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /** The library's construct signature is not generic, so the typed instance is recovered by assertion */
        this.graph = new ForceGraph3D(this.stage, {
            controlType: 'orbit',
            rendererConfig: { antialias: true, alpha: true },
        }) as unknown as ForceGraph3DInstance<SchemaNode, SchemaLink>;

        this.configure();
        this.bindControls();
        this.applyFilters();
        this.observeSize();

        if (this.status) {
            this.status.remove();
        }

        document.addEventListener('livewire:navigating', this.destroy, { once: true });
    }

    /**
     * Reads the JSON payload embedded in the page.
     */
    private readPayload(): SchemaPayload {
        const script = this.root.querySelector<HTMLScriptElement>('[data-dbg-payload]');

        return JSON.parse(script?.textContent ?? '{"nodes":[],"links":[],"domains":{}}') as SchemaPayload;
    }

    /**
     * Applies the static rendering configuration. Under reduced motion
     * the layout is solved during warm-up and then frozen, so nothing
     * drifts on screen once the graph appears.
     */
    private configure(): void {
        this.graph
            .backgroundColor('rgba(0,0,0,0)')
            .showNavInfo(false)
            .nodeRelSize(5)
            .nodeResolution(20)
            .nodeVal((node) => this.nodeValue(node))
            .nodeColor((node) => this.nodeColour(node))
            .nodeLabel((node) => this.nodeTooltip(node))
            .nodeThreeObject((node) => this.nodeLabelSprite(node))
            .nodeThreeObjectExtend(true)
            .linkColor((link) => this.linkColour(link))
            .linkWidth((link) => (this.related.links.has(link) ? 1.6 : 0.6))
            .linkOpacity(0.55)
            .linkCurvature((link) => (link.source === link.target ? 0.6 : 0))
            .linkDirectionalArrowLength(4)
            .linkDirectionalArrowRelPos(1)
            .linkLabel((link) => this.linkTooltip(link))
            .warmupTicks(this.reducedMotion ? 240 : 0)
            .cooldownTicks(this.reducedMotion ? 0 : Infinity)
            .cooldownTime(4000)
            .onNodeHover((node) => this.highlight(node ?? this.selected))
            .onNodeClick((node) => this.select(node))
            .onBackgroundClick(() => this.select(null))
            .onEngineStop(() => this.fit());

        this.graph.d3Force('charge')?.strength(-300);
        this.graph.d3Force('link')?.distance(70);
        this.graph.d3Force('gravity', this.gravity(0.14));
    }

    /**
     * A gentle pull towards the origin.
     *
     * The layout's built-in centring force only translates the whole
     * cloud, so a table with no foreign keys is pushed outwards by
     * repulsion and never comes back. Without this the graph settles
     * roughly four times wider than it needs to be and every table is
     * a speck once the view is framed.
     *
     * @param strength - Attraction per tick, in the same scale as d3's
     *                   positioning forces.
     */
    private gravity(strength: number): (alpha: number) => void {
        let nodes: SchemaNode[] = [];

        const force: { (alpha: number): void; initialize?: (nodes: SchemaNode[]) => void } = (alpha: number): void => {
            nodes.forEach((node) => {
                node.vx = (node.vx ?? 0) - (node.x ?? 0) * strength * alpha;
                node.vy = (node.vy ?? 0) - (node.y ?? 0) * strength * alpha;
                node.vz = (node.vz ?? 0) - (node.z ?? 0) * strength * alpha;
            });
        };

        force.initialize = (simulationNodes: SchemaNode[]): void => {
            nodes = simulationNodes;
        };

        return force;
    }

    /**
     * Sphere volume for a table. Row counts span several orders of
     * magnitude, so they are compressed logarithmically and floored,
     * keeping an empty table visible without letting a busy one swamp
     * the scene.
     *
     * @param node - The table being sized.
     */
    private nodeValue(node: SchemaNode): number {
        return 1 + Math.log10(1 + node.rows) * 3;
    }

    /**
     * The sphere colour, dimmed while another table is highlighted.
     *
     * @param node - The table being coloured.
     */
    private nodeColour(node: SchemaNode): string {
        const colour = this.domain(node).color;

        if (this.related.nodes.size === 0 || this.related.nodes.has(node.id)) {
            return colour;
        }

        return `${colour}33`;
    }

    /**
     * The edge colour, emphasised while one of its endpoints is
     * highlighted.
     *
     * @param link - The foreign key being coloured.
     */
    private linkColour(link: SchemaLink): string {
        if (this.related.links.has(link)) {
            return '#f59e0b';
        }

        return this.related.nodes.size === 0 ? '#94a3b8' : '#94a3b833';
    }

    /**
     * Presentation metadata for a table's domain.
     *
     * @param node - The table being looked up.
     */
    private domain(node: SchemaNode): SchemaDomain {
        return this.payload.domains[node.domain] ?? { label: node.domain, color: '#94a3b8', pill: '#475569' };
    }

    /**
     * Hover tooltip for a table.
     *
     * @param node - The hovered table.
     */
    private nodeTooltip(node: SchemaNode): string {
        return `<strong>${node.id}</strong><br>${node.columnCount}개 컬럼 · ${node.rows.toLocaleString()}행`;
    }

    /**
     * Hover tooltip for a foreign key, naming the columns it joins.
     *
     * @param link - The hovered constraint.
     */
    private linkTooltip(link: SchemaLink): string {
        const source = this.endpoint(link.source);
        const target = this.endpoint(link.target);

        return `${source}.${link.columns.join(', ')} → ${target}.${link.references.join(', ')}`;
    }

    /**
     * A link endpoint as a table name, whether or not the layout has
     * already swapped the name for the node object.
     *
     * @param end - The endpoint to name.
     */
    private endpoint(end: string | SchemaNode): string {
        return typeof end === 'string' ? end : end.id;
    }

    /**
     * A billboarded label drawn on a canvas so it stays crisp and keeps
     * white-on-domain-colour contrast in both the light and the dark
     * panel theme.
     *
     * @param node - The table being labelled.
     */
    private nodeLabelSprite(node: SchemaNode): Sprite {
        const scale = Math.min(window.devicePixelRatio || 1, 2);
        const fontSize = 40;
        const padding = 14;
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d') as CanvasRenderingContext2D;
        const font = `700 ${fontSize}px ui-sans-serif, system-ui, sans-serif`;

        context.font = font;
        const width = Math.ceil(context.measureText(node.id).width) + padding * 2;
        const height = fontSize + padding * 1.5;

        canvas.width = width * scale;
        canvas.height = height * scale;
        context.scale(scale, scale);
        context.font = font;
        context.textAlign = 'center';
        context.textBaseline = 'middle';

        context.fillStyle = this.domain(node).pill;
        context.globalAlpha = 0.94;
        context.beginPath();
        context.roundRect(0, 0, width, height, height / 2);
        context.fill();

        context.globalAlpha = 1;
        context.fillStyle = '#ffffff';
        context.fillText(node.id, width / 2, height / 2 + 1);

        const texture = new CanvasTexture(canvas);
        texture.minFilter = LinearFilter;

        const sprite = new Sprite(new SpriteMaterial({ map: texture, transparent: true, depthWrite: false }));
        const spriteHeight = 7;
        sprite.scale.set((spriteHeight * width) / height, spriteHeight, 1);
        sprite.position.set(0, Math.cbrt(this.nodeValue(node)) * 5 + spriteHeight * 0.8, 0);

        this.labels.set(node.id, sprite);

        return sprite;
    }

    /**
     * Wires the legend filters, the system table toggle and the panel's
     * close button.
     */
    private bindControls(): void {
        this.root.querySelectorAll<HTMLButtonElement>('[data-dbg-domain]').forEach((button) => {
            button.addEventListener('click', () => {
                const domain = button.dataset.dbgDomain as string;

                if (this.hiddenDomains.has(domain)) {
                    this.hiddenDomains.delete(domain);
                } else {
                    this.hiddenDomains.add(domain);
                }

                button.setAttribute('aria-pressed', String(!this.hiddenDomains.has(domain)));
                this.applyFilters();
            });
        });

        const systemToggle = this.root.querySelector<HTMLInputElement>('[data-dbg-system]');

        systemToggle?.addEventListener('change', () => {
            this.showSystem = systemToggle.checked;
            this.applyFilters();
        });
    }

    /**
     * Rebuilds the rendered graph from the current filters. Node
     * objects are reused so the layout keeps its positions across a
     * toggle, while links are always fed as fresh objects because the
     * force engine rewrites their endpoints in place.
     */
    private applyFilters(): void {
        const nodes = this.payload.nodes.filter(
            (node) => (this.showSystem || !node.system) && !this.hiddenDomains.has(node.domain),
        );
        const visible = new Set(nodes.map((node) => node.id));
        const links = this.payload.links
            .filter((link) => visible.has(this.endpoint(link.source)) && visible.has(this.endpoint(link.target)))
            .map((link) => ({ ...link, source: this.endpoint(link.source), target: this.endpoint(link.target) }));

        if (this.selected && !visible.has(this.selected.id)) {
            this.select(null);
        }

        this.labels.clear();
        this.graph.graphData({ nodes, links });
        this.updateCounts(nodes.length, links.length);

        /**
         * The layout is applied on a later frame than this call, so framing it has to wait:
         * briefly under reduced motion, where the warm-up has already solved the positions,
         * and once mid-flight otherwise so the graph does not sit off screen while it
         * spreads. An animated layout is framed again by onEngineStop once it settles.
         */
        window.setTimeout(() => this.fit(), this.reducedMotion ? 150 : 1200);
    }

    /**
     * Frames the whole graph in the viewport once the layout settles.
     */
    private fit(): void {
        if (this.graph.graphData().nodes.length === 0) {
            return;
        }

        this.graph.zoomToFit(this.reducedMotion ? 0 : 400, 60);
    }

    /**
     * Writes the visible table and relationship totals into the
     * toolbar.
     *
     * @param tables - Number of tables currently drawn.
     * @param relations - Number of foreign keys currently drawn.
     */
    private updateCounts(tables: number, relations: number): void {
        const counter = this.root.querySelector<HTMLElement>('[data-dbg-counts]');

        if (counter) {
            counter.textContent = `테이블 ${tables} · 관계 ${relations}`;
        }
    }

    /**
     * Highlights a table and everything it is related to, dimming the
     * rest of the scene. Passing null clears the highlight.
     *
     * @param node - The table to highlight, or null.
     */
    private highlight(node: SchemaNode | null): void {
        this.related = { nodes: new Set<string>(), links: new Set<SchemaLink>() };

        if (node) {
            this.related.nodes.add(node.id);

            this.graph.graphData().links.forEach((link) => {
                const source = this.endpoint(link.source);
                const target = this.endpoint(link.target);

                if (source === node.id || target === node.id) {
                    this.related.links.add(link);
                    this.related.nodes.add(source);
                    this.related.nodes.add(target);
                }
            });
        }

        this.labels.forEach((sprite, id) => {
            sprite.material.opacity = this.related.nodes.size === 0 || this.related.nodes.has(id) ? 1 : 0.18;
        });

        /** Re-setting an accessor is how the force graph is told to repaint */
        this.graph.nodeColor(this.graph.nodeColor()).linkColor(this.graph.linkColor()).linkWidth(this.graph.linkWidth());
    }

    /**
     * Selects a table: flies the camera to it and fills the detail
     * panel. Passing null clears the selection.
     *
     * @param node - The table to focus, or null.
     */
    private select(node: SchemaNode | null): void {
        this.selected = node;
        this.highlight(node);
        this.renderPanel(node);

        if (!node || node.x === undefined) {
            return;
        }

        /** Roughly the width of a settled layout, so the focused table is centred without losing its surroundings */
        const distance = 280;
        const ratio = 1 + distance / Math.hypot(node.x, node.y ?? 0, node.z ?? 0);

        this.graph.cameraPosition(
            { x: node.x * ratio, y: (node.y ?? 0) * ratio, z: (node.z ?? 0) * ratio },
            { x: node.x, y: node.y ?? 0, z: node.z ?? 0 },
            this.reducedMotion ? 0 : 700,
        );
    }

    /**
     * Renders the detail panel for a table: its columns with types, and
     * its outgoing and incoming foreign keys.
     *
     * @param node - The selected table, or null for the empty state.
     */
    private renderPanel(node: SchemaNode | null): void {
        this.panel.textContent = '';

        if (!node) {
            this.panel.append(this.hint('테이블을 선택하면 컬럼과 관계가 표시됩니다.'));

            return;
        }

        const heading = document.createElement('h3');
        heading.className = 'dbg-panel-title';
        heading.textContent = node.id;

        const meta = document.createElement('p');
        meta.className = 'dbg-panel-meta';
        meta.textContent = `${this.domain(node).label} · ${node.columnCount}개 컬럼 · ${node.rows.toLocaleString()}행`;

        this.panel.append(heading, meta, this.columnList(node));

        const outgoing = this.payload.links.filter((link) => this.endpoint(link.source) === node.id);
        const incoming = this.payload.links.filter((link) => this.endpoint(link.target) === node.id);

        if (outgoing.length) {
            this.panel.append(
                this.subheading('참조하는 테이블'),
                this.relationList(
                    outgoing,
                    (link) => `${link.columns.join(', ')} → ${this.endpoint(link.target)}.${link.references.join(', ')}`,
                ),
            );
        }

        if (incoming.length) {
            this.panel.append(
                this.subheading('참조되는 테이블'),
                this.relationList(
                    incoming,
                    (link) => `${this.endpoint(link.source)}.${link.columns.join(', ')} → ${link.references.join(', ')}`,
                ),
            );
        }
    }

    /**
     * The column table for the detail panel.
     *
     * @param node - The selected table.
     */
    private columnList(node: SchemaNode): HTMLElement {
        const list = document.createElement('ul');
        list.className = 'dbg-columns';

        node.columns.forEach((column) => {
            const item = document.createElement('li');

            const name = document.createElement('span');
            name.className = 'dbg-column-name';
            name.textContent = column.name;

            if (column.primary) {
                name.append(this.badge('PK'));
            }

            if (column.foreign) {
                name.append(this.badge('FK'));
            }

            const type = document.createElement('span');
            type.className = 'dbg-column-type';
            type.textContent = column.nullable ? `${column.type}?` : column.type;

            item.append(name, type);
            list.append(item);
        });

        return list;
    }

    /**
     * A relationship list for the detail panel.
     *
     * @param links - The constraints to list.
     * @param describe - Formats one constraint as a line of text.
     */
    private relationList(links: SchemaLink[], describe: (link: SchemaLink) => string): HTMLElement {
        const list = document.createElement('ul');
        list.className = 'dbg-relations';

        links.forEach((link) => {
            const item = document.createElement('li');
            item.textContent = describe(link);
            list.append(item);
        });

        return list;
    }

    /**
     * A small pill used to flag primary and foreign key columns.
     *
     * @param text - The pill text.
     */
    private badge(text: string): HTMLElement {
        const badge = document.createElement('span');
        badge.className = 'dbg-badge';
        badge.textContent = text;

        return badge;
    }

    /**
     * A section subheading inside the detail panel.
     *
     * @param text - The heading text.
     */
    private subheading(text: string): HTMLElement {
        const heading = document.createElement('h4');
        heading.className = 'dbg-panel-subtitle';
        heading.textContent = text;

        return heading;
    }

    /**
     * The panel's empty state.
     *
     * @param text - The message to show.
     */
    private hint(text: string): HTMLElement {
        const paragraph = document.createElement('p');
        paragraph.className = 'dbg-panel-hint';
        paragraph.textContent = text;

        return paragraph;
    }

    /**
     * Keeps the canvas matched to its container, which is sized in
     * relative units so it works from a phone up to a wide desktop.
     */
    private observeSize(): void {
        const resize = (): void => {
            this.graph.width(this.stage.clientWidth).height(this.stage.clientHeight);
        };

        resize();

        this.resizeObserver = new ResizeObserver(resize);
        this.resizeObserver.observe(this.stage);
    }

    /**
     * Releases the WebGL context when the page is left behind.
     */
    private destroy = (): void => {
        this.resizeObserver?.disconnect();
        this.graph._destructor();
    };
}
