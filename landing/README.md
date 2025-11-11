# PapaM VoIP Landing Page

A modern, animated landing page for PapaM VoIP service with purple theme and rich animations.

## Features

- 🎨 **Modern Design**: Purple gradient theme with glassmorphism effects
- ✨ **Rich Animations**: Particle system, smooth transitions, hover effects
- 📱 **Responsive**: Works perfectly on all devices
- 🌍 **Multi-language**: Turkish and English support
- 🚀 **Performance Optimized**: Fast loading with optimized assets
- 📞 **VoIP Focused**: Specifically designed for VoIP service presentation

## Directory Structure

```
landing/
├── assets/
│   ├── css/
│   │   └── landing-animations.css    # Custom animations and styles
│   └── js/
│       └── landing-animations.js     # Animation engine and interactions
├── includes/
│   ├── LangHelper.php               # Language management helper
│   └── router.php                   # Simple routing system
├── lang/
│   ├── tr.php                       # Turkish translations
│   └── en.php                       # English translations
├── .htaccess                        # Apache configuration
├── index.php                        # Main landing page
└── README.md                        # This file
```

## Installation

1. **Upload Files**: Upload all files to your web server
2. **Web Server Setup**: Ensure Apache mod_rewrite is enabled
3. **Permissions**: Make sure files have proper read permissions
4. **Test**: Visit your domain to see the landing page

## Configuration

### Language Settings
- Default language: Turkish (tr)
- Supported languages: Turkish (tr), English (en)
- Language detection: URL parameter > Session > Browser language

### Contact Information
- Primary Telegram: @lionmw
- Support Telegram: @Itsupportemre

### Pricing (Current)
- **SIP Accounts**: $250 per SIP (one-time payment)
- **Fixed Numbers**: $400 setup + $70/month

## Features Included

### Animated Sections
- **Header**: Glassmorphism navigation with animated logo
- **Hero**: Particle background with typewriter effect
- **Features**: 6 feature cards with hover animations
- **How It Works**: 4-step animated process
- **Pricing**: Animated pricing cards with popular badge
- **Footer**: Social links and company information

### Interactive Elements
- Particle system background animation
- Smooth scroll navigation
- Mobile-responsive hamburger menu
- Language switcher dropdown
- Hover effects on all interactive elements
- Counter animations for statistics

### Technical Features
- Pure CSS animations (no jQuery required)
- Vanilla JavaScript for interactions
- Tailwind CSS framework integration
- Font Awesome icons
- Google Fonts (Inter)
- SEO optimized meta tags
- Open Graph tags for social sharing

## Customization

### Colors
The purple theme can be customized in `assets/css/landing-animations.css`:
```css
:root {
    --purple-primary: #8B5CF6;
    --purple-secondary: #7C3AED;
    --purple-dark: #6D28D9;
    /* Add more custom colors here */
}
```

### Content
Edit translations in:
- `lang/tr.php` for Turkish content
- `lang/en.php` for English content

### Styling
Modify animations and styles in:
- `assets/css/landing-animations.css`

### Functionality
Add custom JavaScript in:
- `assets/js/landing-animations.js`

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **Optimized CSS**: Minified and cached
- **Efficient Animations**: GPU-accelerated transforms
- **Compressed Assets**: GZIP compression enabled
- **Cached Resources**: Browser caching configured

## SEO Features

- Semantic HTML structure
- Meta descriptions and keywords
- Open Graph tags
- Structured data ready
- Fast loading times
- Mobile-friendly design

## Security

- XSS protection headers
- Content type validation
- Frame options security
- Secure file permissions
- Input validation and sanitization

## Contact & Support

For technical support or customization:
- Telegram: @Itsupportemre
- Sales: @lionmw

## License

Copyright © 2025 PapaM VoIP. All rights reserved.

---

**Note**: This landing page is designed specifically for PapaM VoIP service. All branding, colors, and content are tailored for VoIP service presentation.