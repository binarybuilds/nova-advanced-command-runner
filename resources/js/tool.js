Nova.booting((Vue, router) => {
    router.addRoutes([
        {
            name: 'nova-advanced-command-runner',
            path: '/nova-advanced-command-runner',
            component: require('./components/Tool'),
        },
    ])
})
