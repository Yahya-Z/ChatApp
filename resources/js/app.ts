import './bootstrap';
import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

// اسم التطبيق
const appName = 'تطبيق الدردشة';

// كود لتشخيص مشاكل تحميل الصفحات
console.log('تهيئة تطبيق Inertia...');

// @ts-ignore - نتجاهل أخطاء TypeScript للتبسيط
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        console.log('إعداد التطبيق مع المكونات:', { page: props.initialPage?.component });
        
        const app = createApp({
            render: () => h(App, props)
        });
        
        app.use(plugin);
        app.use(ZiggyVue);
        
        app.mount(el);
        
        console.log('تم تركيب التطبيق بنجاح!');
    },
    progress: {
        color: '#4B5563',
    },
});