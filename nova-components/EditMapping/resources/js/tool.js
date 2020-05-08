Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'edit-mapping',
      path: '/edit-mapping',
      component: require('./components/Tool'),
    },
  ])
})
