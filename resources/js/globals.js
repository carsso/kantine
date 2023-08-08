import _ from 'lodash';

export default {
  install(app) {
    const componentFiles = import.meta.globEager(
      './components/*.vue'
    );

    Object.entries(componentFiles).forEach(([path, m]) => {
      const componentName = _.upperFirst(
        _.camelCase(path.split('/').pop().replace(/\.\w+$/, ''))
      );

      app.component(
        `${componentName}`, m.default
      );
    })
  },
};

