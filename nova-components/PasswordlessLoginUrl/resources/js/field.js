Nova.booting((Vue, router, store) => {
  Vue.component('index-passwordless-login-url', require('./components/IndexField'))
  Vue.component('detail-passwordless-login-url', require('./components/DetailField'))
  Vue.component('form-passwordless-login-url', require('./components/FormField'))
})
