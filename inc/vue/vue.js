import Vue from 'vue';

import googleApi from './pages/googleApi';

let app = document.querySelector('[data-vue=google-api]');
if ( app ) {
  /* eslint-disable no-new */
  new Vue({
    el: app,
    render: h => h(googleApi),
  });
}