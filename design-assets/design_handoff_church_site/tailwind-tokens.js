/**
 * Tailwind theme extension for the Brisbane Ju-neun Church design.
 * Merge into tailwind.config.js under theme.extend.
 */
module.exports = {
  colors: {
    navy: { DEFAULT: '#16223c', 900: '#0d1730', 700: '#233559', 400: '#7688aa' },
    cream: '#f4f1ea',
    paper: '#ffffff',
    accent: { DEFAULT: '#004aad', 700: '#00337a', 100: '#dce7f7' },
  },
  fontFamily: {
    sans: ['Archivo', 'system-ui', 'sans-serif'],
    kr: ['GmarketSans', 'Gothic A1', 'Archivo', 'system-ui', 'sans-serif'],
  },
  fontSize: {
    kicker: ['0.6875rem', { lineHeight: '1', letterSpacing: '0.16em' }],
    'display-lg': ['clamp(2.1rem, 5.6vw, 3rem)', { lineHeight: '1', letterSpacing: '-0.01em' }],
    'display-md': ['clamp(1.6rem, 4.2vw, 2.25rem)', { lineHeight: '1.11' }],
    'display-sm': ['1.25rem', { lineHeight: '1.2' }],
  },
  borderRadius: { nav: '8px', btn: '10px', media: '12px', play: '14px', frame: '18px' },
  borderColor: { line: 'rgba(22,34,60,.16)' },
  maxWidth: { container: '80rem' },
  screens: { md: '768px', lg: '1024px', xl: '1280px' },
};
