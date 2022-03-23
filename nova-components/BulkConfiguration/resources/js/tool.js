Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'bulk-configuration',
      path: '/bulk-configuration',
      component: require('./components/Tool'),
    },
  ])
})
