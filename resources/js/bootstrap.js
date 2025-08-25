import { createApp, h } from "vue";
import { createInertiaApp, Head, Link, router } from "@inertiajs/vue3";
import { Dialog, Loading, Notify, Quasar } from "quasar";
import { ZiggyVue } from '../../vendor/tightenco/ziggy/src/js';
import axios from 'axios';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
import relativeTime from 'dayjs/plugin/relativeTime'
import "@quasar/extras/material-icons/material-icons.css";
import "@quasar/extras/material-icons-outlined/material-icons-outlined.css";
import "@quasar/extras/material-symbols-outlined/material-symbols-outlined.css";
import "@quasar/extras/fontawesome-v6/fontawesome-v6.css";
import "quasar/src/css/index.sass";

import MyLink from "@/components/MyLink.vue";
import GlobalPlugin from '@/plugins';
import processFlashMessage from "@/helpers/flash-message";

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

dayjs.extend(relativeTime)
dayjs.locale('id');

router.on('success', processFlashMessage);

export default function boot({ layouts, pagesGlob }) {
  createInertiaApp({
    title: (title) =>
      window.CONFIG.APP_NAME + (title ? " - " + title : ""),
    resolve: (name) => {
      return pagesGlob[`./pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
      const VueApp = createApp({ render: () => h(App, props) })
        .use(plugin)
        .use(ZiggyVue)
        .use(Quasar, {
          plugins: { Notify, Loading, Dialog },
          config: { iconSet: "material-symbols-outlined" },
        })
        .component("i-head", Head)
        .component("i-link", Link)
        .component("my-link", MyLink)
        .component("authenticated-layout", layouts.authenticated)
        .component("guest-layout", layouts.guest);

      VueApp.use(GlobalPlugin);
      VueApp.mount(el);
    },
    progress: { color: "#4B5563" },
  });
}
