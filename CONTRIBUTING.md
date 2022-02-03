# Contributing

## Local development

### Setting up an environment

This plugin uses [craft-plugin-vite][] to bundle its CP frontend assets.

This is how you start the HMR server:

```bash
cd buildchain
npm install
npm run dev
```

You must also tell [craft-plugin-vite][] to use the HMR server by setting the 
`VITE_PLUGIN_DEVSERVER` environment variable to a truthy value.  
Example:

```.dotenv
VITE_PLUGIN_DEVSERVER="true"
```

### Committing frontend changes

Before committing your frontend changes, don't forget to build them and include 
them in your commit:

```bash
cd builchain
npm run build
git add ../src/web/assets/dits
```

[craft-plugin-vite]: https://github.com/nystudio107/craft-plugin-vite
