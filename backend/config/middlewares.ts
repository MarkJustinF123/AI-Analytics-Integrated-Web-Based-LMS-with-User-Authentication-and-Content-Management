export default [
  'strapi::logger',
  'strapi::errors',
  'strapi::security',
  
  {
    name: 'strapi::cors',
    config: {
      enabled: true,
      // Add the Strapi development port (1337)
      origin: ['http://localhost', 'http://127.0.0.1', 'http://localhost:1337'], 
      headers: '*',
    },
  },
  
  'strapi::poweredBy',
  'strapi::query',
  'strapi::body',
  'strapi::session',
  'strapi::favicon',
  'strapi::public',
];