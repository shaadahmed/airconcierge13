// https://router.vuejs.org/api/interfaces/routeroptions.html
export default {
  routes: scannedRoutes => [
    ...scannedRoutes.filter(route => route.path !== '/' && route.name !== 'index'),
    {
      path: '/',
      name: 'index',
      redirect: '/admin/dashboard',
    },
    {
      path: '/dashboard',
      name: 'dashboard-alias',
      redirect: '/admin/dashboard',
    },
  ],
}
