const mix = require('laravel-mix')

mix.setResourceRoot('src/resources/vendor')
mix.js('./src/resources/vendor/stripe/assets/scripts', './dist')
mix.postCss('./src/resources/vendor/stripe/assets/styles/stripe.css', './dist', [])
mix.copy('./src/resources/vendor/stripe/svg', './dist/svg')