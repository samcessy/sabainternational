import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Public-facing marketing pages get PublicLayout (header/footer/skip-link).
// Extend this list as more single-file public pages are built; grouped page
// types (programs/*, stories/*, ...) use a prefix match instead so adding a
// new page to an existing group can't silently fall through to the admin
// AppLayout by default, as happened here before this comment existed.
const publicPages = [
    'Home',
    'Give',
    'GiveThankYou',
    'About',
    'Contact',
    'Volunteer',
    'Partner',
    'Error',
];

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case publicPages.includes(name):
            case name.startsWith('programs/'):
            case name.startsWith('stories/'):
            case name.startsWith('pages/'):
            case name.startsWith('documents/'):
            case name.startsWith('events/'):
                return PublicLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            case name.startsWith('admin/'):
                return AppLayout;
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
