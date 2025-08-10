const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

// Enable more detailed error reporting
config.resolver.platforms = ['ios', 'android', 'native', 'web'];

// Better debugging
config.transformer = {
  ...config.transformer,
  minifierConfig: {
    keep_quotes: true,
  },
};

// Improve development experience
config.server = {
  ...config.server,
  enhanceMiddleware: (middleware) => {
    return (req, res, next) => {
      // Log all requests for debugging
      console.log(`📡 ${req.method} ${req.url}`);
      return middleware(req, res, next);
    };
  },
};

module.exports = config;
