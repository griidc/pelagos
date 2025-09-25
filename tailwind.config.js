/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './assets/**/*.js',
    './assets/scss/components/*.scss',
    './templates/**/*.html.twig',
  ],
  theme: {
    extend: {
      screens: {
        '3xl': '1920px',
        '4xl': '2560px',
      },
      height: {
        60: '13.125rem',
        80: '24.375rem',
      },
      boxShadow: {
        'tile': '0px 4px 30px 0px rgba(0, 0, 0, 0.06)',
      },
      backgroundImage: {
        'image-overlay-blue': 'linear-gradient(178deg, #233876 22.75%, rgba(0, 0, 0, 0.00) 100%)',
        'image-overlay-black': 'linear-gradient(178deg, #000 22.75%, rgba(0, 0, 0, 0.00) 75%)',
      },
      colors: {
        primary: 'var(--primary)',
        secondary: 'var(--secondary)',
        alternate: 'var(--alternate)',
        accent: 'var(--accent-color)',
        available: 'var(--available)',
        restricted: 'var(--restricted)',
        submitted: 'var(--submitted)',
        identified: 'var(--identified)',
        remotlyhosted: 'var(--remotlyhosted)',
        erddap: 'var(--erddap)',
        ncei: 'var(--ncei)',
        itemtype: 'var(--itemtype)',
        coldstorage: 'var(--coldstorage)',
      },
    },
    fontFamily: {
      'dm-sans': ['dm-sans', 'Helvetica', 'sans-serif'],
    },
    typography: (theme) => ({
      DEFAULT: {
        css: [
          {
            color: 'inherit',
            // fontWeight: '400',
            // fontFamily: 'dm-sans, Helvetica, sans-serif',
            fontSize: theme('fontSize.base'),
            lineHeight: theme('lineHeight.base'),
            letterSpacing: '0.01em',
            transition: 'opacity 0.35s ease-out',
            'a, p a': {
              fontWeight: '400',
              textDecoration: 'none',
              fontSize: 'inherit',
              lineHeight: 'inherit',
              cursor: 'pointer',
              transition: 'opacity 0.35s ease-out',
            },
            strong: {
              fontWeight: '700',
            },
            'h1, h2, h3, h4, h5, h6': {
              wordBreak: 'break-word',
              marginTop: '0',
              fontStyle: 'normal',
            },
            '.prose h1': {
              fontSize: '2.625rem',
              lineHeight: '3.25rem',
              marginBottom: '1.5rem',
              fontWeight: 700,
              '@media (min-width: 768px)': {
                fontSize: '3.875rem',
                lineHeight: '5.813rem',
              },
              '@media (min-width: 1280px)': {
                fontSize: '4.5rem',
                lineHeight: '6.75rem',
              },
            },
            '.prose h2': {
              fontSize: '1.875rem',
              lineHeight: '2.813rem',
              marginBottom: '0.875rem',
              fontWeight: '700',
              '@media (min-width: 768px)': {
                fontSize: '2.25rem',
                lineHeight: '3.375rem',
              },
            },
            '.prose h3': {
              fontSize: '1.625rem',
              lineHeight: '2.25rem',
              marginBottom: '0.438rem',
              fontWeight: '700',
              '@media (min-width: 768px)': {
                fontSize: '1.875rem',
                lineHeight: '2.813rem',
              },
            },
            '.prose h4': {
              fontSize: '1.125rem',
              lineHeight: '1.688rem',
              marginBottom: '0.625rem',
              fontWeight: '700',
            },
            '.prose p': {
              fontWeight: 400,
              fontSize: '1.125rem',
              lineHeight: '1.688rem',
              marginBottom: '1.125rem',
            },
            '.prose ul, .prose ol': {
              fontSize: '1.125rem',
              lineHeight: '1.688rem',
              marginBottom: '1.125rem',
            },
            '.prose li': {
              marginBottom: '1.125rem',
            },
            '.prose h1:first-child , .prose h2:first-child , .prose h3:first-child , .prose h4:first-child , .prose h5:first-child , .prose h6:first-child ':
              {
                marginTop: 0,
              },
          },
        ],
        md: {
          css: [
            {
              fontSize: '1.125rem',
              lineHeight: '1.75rem',
            },
          ],
        },
      },
    }),
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
};
