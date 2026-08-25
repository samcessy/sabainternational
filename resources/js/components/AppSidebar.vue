<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CalendarDays,
    CircleDollarSign,
    FileText,
    FolderGit2,
    HandHeart,
    Image,
    LayoutGrid,
    Mail,
    Megaphone,
    Newspaper,
    NotebookText,
    ScrollText,
    Send,
    TrendingUp,
    UserCheck,
    Users,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as auditLogsIndex } from '@/routes/admin/audit-logs';
import { index as campaignsIndex } from '@/routes/admin/campaigns';
import { index as contactSubmissionsIndex } from '@/routes/admin/contact-submissions';
import { index as documentsIndex } from '@/routes/admin/documents';
import { index as donationsIndex } from '@/routes/admin/donations';
import { index as eventsIndex } from '@/routes/admin/events';
import { index as impactMetricsIndex } from '@/routes/admin/impact-metrics';
import { index as mediaIndex } from '@/routes/admin/media';
import { index as newsletterSubscribersIndex } from '@/routes/admin/newsletter-subscribers';
import { index as partnershipInquiriesIndex } from '@/routes/admin/partnership-inquiries';
import { index as programsIndex } from '@/routes/admin/programs';
import { index as storiesIndex } from '@/routes/admin/stories';
import { index as teamMembersIndex } from '@/routes/admin/team-members';
import { index as usersIndex } from '@/routes/admin/users';
import { index as volunteerApplicationsIndex } from '@/routes/admin/volunteer-applications';
import type { Auth, NavItem } from '@/types';

const dashboardUrl = computed(() => dashboard().url);
const page = usePage<{ auth: Auth }>();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboardUrl.value,
            icon: LayoutGrid,
        },
    ];

    if (page.props.auth.permissions.includes('content:view')) {
        items.push(
            {
                title: 'Programs',
                href: programsIndex.url(),
                icon: Newspaper,
            },
            {
                title: 'Stories',
                href: storiesIndex.url(),
                icon: NotebookText,
            },
            {
                title: 'Team Members',
                href: teamMembersIndex.url(),
                icon: UsersRound,
            },
            {
                title: 'Media Library',
                href: mediaIndex.url(),
                icon: Image,
            },
            {
                title: 'Documents',
                href: documentsIndex.url(),
                icon: FileText,
            },
            {
                title: 'Events',
                href: eventsIndex.url(),
                icon: CalendarDays,
            },
        );
    }

    if (page.props.auth.permissions.includes('engagement:view')) {
        items.push(
            {
                title: 'Contact Messages',
                href: contactSubmissionsIndex.url(),
                icon: Mail,
            },
            {
                title: 'Volunteer Applications',
                href: volunteerApplicationsIndex.url(),
                icon: UserCheck,
            },
            {
                title: 'Partnership Inquiries',
                href: partnershipInquiriesIndex.url(),
                icon: HandHeart,
            },
            {
                title: 'Newsletter Subscribers',
                href: newsletterSubscribersIndex.url(),
                icon: Send,
            },
        );
    }

    return items;
});

const impactNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (page.props.auth.permissions.includes('impact:view')) {
        items.push({
            title: 'Impact Metrics',
            href: impactMetricsIndex.url(),
            icon: TrendingUp,
        });
    }

    return items;
});

const fundraisingNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (page.props.auth.permissions.includes('fundraising:view')) {
        items.push(
            {
                title: 'Campaigns',
                href: campaignsIndex.url(),
                icon: Megaphone,
            },
            {
                title: 'Donations',
                href: donationsIndex.url(),
                icon: CircleDollarSign,
            },
        );
    }

    return items;
});

const systemNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (page.props.auth.permissions.includes('system:manage-users')) {
        items.push({
            title: 'Users',
            href: usersIndex.url(),
            icon: Users,
        });
    }

    if (page.props.auth.permissions.includes('system:view-audit-logs')) {
        items.push({
            title: 'Audit Logs',
            href: auditLogsIndex.url(),
            icon: ScrollText,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain
                v-if="impactNavItems.length > 0"
                :items="impactNavItems"
                label="Impact"
            />
            <NavMain
                v-if="fundraisingNavItems.length > 0"
                :items="fundraisingNavItems"
                label="Fundraising"
            />
            <NavMain
                v-if="systemNavItems.length > 0"
                :items="systemNavItems"
                label="System"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
