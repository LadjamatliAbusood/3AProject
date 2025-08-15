import './bootstrap';
import '../css/app.css';
import Swal from 'sweetalert2';
window.Swal = Swal;
import { createApp, h } from 'vue'
import { createInertiaApp, Head, Link } from '@inertiajs/vue3'
import {ZiggyVue} from '../../vendor/tightenco/ziggy'
import Layout from './Layouts/Layout.vue';
import CashierLayout from './Layouts/CashierLayout.vue';
import  * as HeroIcons from '@heroicons/vue/24/solid'
import AOS from 'aos';
import 'aos/dist/aos.css';


createInertiaApp({
    title: (title) => `A&A Merchandiser ${title}`,
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    let page = pages[`./Pages/${name}.vue`]
    page.default.layout = page.default.layout === undefined ? Layout : page.default.layout 
    return page;
  },
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) })
    for (const [key, component] of Object.entries(HeroIcons)) {
      vueApp.component(key, component)
    }

    vueApp
      .use(plugin)
      .use(ZiggyVue)
      .component("Head", Head)
      .component("Link", Link)
      .mount(el);
      import('aos').then(AOS => {
        AOS.init();
    });
  },
    progress:{
    // delay: 250,
    color: 'blue',
    includeCSS: true,
    showSpinner: true,

  }
})