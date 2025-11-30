<script setup>
import { ref, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import Avatar from "@/Components/Avatar.vue";

defineProps({
    title: String,
});

const page = usePage();
const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(true);

// Check if user is admin
const isAdmin = computed(() => {
    return page.props.auth?.user?.user_type === "admin";
});

// Check if user is volunteer
const isVolunteer = computed(() => {
    return page.props.auth?.user?.user_type === "volunteer";
});

// Check if user is resident
const isResident = computed(() => {
    return page.props.auth?.user?.user_type === "resident";
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <!-- Skip to main content link -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-white focus:text-black focus:rounded focus:shadow-lg"
        >
            Skip to main content
        </a>

        <Banner />

        <div class="min-h-screen">
            <nav
                class="bg-primary sticky top-0 z-50 shadow-md"
                role="navigation"
                aria-label="Main navigation"
            >
                <!-- Primary Navigation Menu -->
                <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-12">
                        <div class="flex items-center">
                            <!-- Sidebar Toggle Button (visible on md+ screens) -->
                            <button
                                @click="toggleSidebar"
                                :aria-expanded="isSidebarOpen"
                                aria-label="Toggle sidebar navigation"
                                aria-controls="sidebar-navigation"
                                class="md:flex hidden items-center justify-center p-2 rounded-md text-white hover:bg-hover focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary transition-colors mr-4"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="size-6"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M3 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 5.25Zm0 4.5A.75.75 0 0 1 3.75 9h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 9.75Zm0 4.5a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Zm0 4.5a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>

                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link
                                    :href="route('dashboard')"
                                    aria-label="Bridge Pen Pal - Go to dashboard"
                                >
                                    <img
                                        src="/images/logos/logo_name_horizontal_white.svg"
                                        alt="Bridge Pen Pal"
                                        class="block h-12 w-auto"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links - Moved to sidebar -->
                            <div class="hidden">
                                <!-- Admin Navigation -->
                                <template v-if="isAdmin">
                                    <NavLink
                                        :href="route('admin.dashboard')"
                                        :active="
                                            route().current('admin.dashboard')
                                        "
                                    >
                                        Overview
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.residents')"
                                        :active="
                                            route().current('admin.residents')
                                        "
                                    >
                                        Residents
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.volunteers')"
                                        :active="
                                            route().current('admin.volunteers')
                                        "
                                    >
                                        Volunteers
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.reports')"
                                        :active="
                                            route().current('admin.reports')
                                        "
                                    >
                                        Reports
                                    </NavLink>
                                </template>

                                <!-- Volunteer/Resident Navigation -->
                                <template v-else>
                                    <NavLink
                                        :href="route('platform.home')"
                                        :active="
                                            route().current('platform.home')
                                        "
                                    >
                                        Home
                                    </NavLink>
                                    <NavLink
                                        :href="route('profile.show')"
                                        :active="
                                            route().current('profile.show') ||
                                            $page.url.startsWith(
                                                '/user/profile'
                                            )
                                        "
                                    >
                                        Profile
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown
                                    align="right"
                                    width="48"
                                    aria-label="User menu"
                                    :content-classes="[
                                        'py-1',
                                        'bg-pressed',
                                        'overflow-hidden',
                                    ]"
                                >
                                    <template #trigger="{ open }">
                                        <button
                                            type="button"
                                            :aria-expanded="
                                                open ? 'true' : 'false'
                                            "
                                            aria-haspopup="true"
                                            aria-label="User menu"
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary transition"
                                        >
                                            <Avatar
                                                :src="
                                                    $page.props.auth.user
                                                        ?.profile_photo_url ||
                                                    null
                                                "
                                                :name="
                                                    $page.props.auth.user
                                                        ?.name || ''
                                                "
                                                size="sm"
                                                border-color="white"
                                            />
                                        </button>
                                    </template>

                                    <template #content>
                                        <!-- User Name (non-clickable) -->
                                        <div
                                            class="px-4 py-3 text-white border-b border-white/20"
                                        >
                                            <p class="text-sm font-medium">
                                                {{ $page.props.auth.user.name }}
                                            </p>
                                        </div>

                                        <DropdownLink
                                            :href="route('profile.show')"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-4"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                Profile
                                            </div>
                                        </DropdownLink>

                                        <div class="border-t border-white/20" />

                                        <!-- Authentication -->
                                        <form
                                            @submit.prevent="logout"
                                            class="block"
                                        >
                                            <DropdownLink as="button">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="currentColor"
                                                        class="size-4"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M16.5 3.75a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5h-6a1.5 1.5 0 0 1-1.5-1.5V15a.75.75 0 0 0-1.5 0v3.75a3 3 0 0 0 3 3h6a3 3 0 0 0 3-3V5.25a3 3 0 0 0-3-3h-6a3 3 0 0 0-3 3V9A.75.75 0 1 0 9 9V5.25a1.5 1.5 0 0 1 1.5-1.5h6Zm-5.03 4.72a.75.75 0 0 0 0 1.06l1.72 1.72H2.25a.75.75 0 0 0 0 1.5h10.94l-1.72 1.72a.75.75 0 1 0 1.06 1.06l3-3a.75.75 0 0 0 0-1.06l-3-3a.75.75 0 0 0-1.06 0Z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                    Log Out
                                                </div>
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger (visible only on portrait mobile, hidden on md+ screens) -->
                        <div class="-me-2 flex items-center md:hidden">
                            <button
                                :aria-expanded="showingNavigationDropdown"
                                aria-label="Toggle mobile navigation menu"
                                aria-controls="mobile-navigation-menu"
                                class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-white hover:bg-hover focus:outline-none focus:bg-hover focus:text-white focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary transition duration-150 ease-in-out"
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            >
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu (visible only on portrait mobile when hamburger is clicked) -->
                <div
                    id="mobile-navigation-menu"
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="md:hidden"
                    aria-label="Mobile navigation menu"
                >
                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-white/20 text-white">
                        <div class="flex items-center px-4">
                            <div class="shrink-0 me-3">
                                <Avatar
                                    :src="
                                        $page.props.auth.user
                                            ?.profile_photo_url || null
                                    "
                                    :name="$page.props.auth.user?.name || ''"
                                    size="md"
                                    border-color="white"
                                />
                            </div>

                            <div>
                                <div class="font-medium text-base text-white">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-white/80">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-3 space-y-1"
                            role="group"
                            aria-label="User account options"
                        >
                            <ResponsiveNavLink
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                <div class="flex items-center gap-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-4"
                                        aria-hidden="true"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    Profile
                                </div>
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    <div class="flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            class="size-4"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M16.5 3.75a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5h-6a1.5 1.5 0 0 1-1.5-1.5V15a.75.75 0 0 0-1.5 0v3.75a3 3 0 0 0 3 3h6a3 3 0 0 0 3-3V5.25a3 3 0 0 0-3-3h-6a3 3 0 0 0-3 3V9A.75.75 0 1 0 9 9V5.25a1.5 1.5 0 0 1 1.5-1.5h6Zm-5.03 4.72a.75.75 0 0 0 0 1.06l1.72 1.72H2.25a.75.75 0 0 0 0 1.5h10.94l-1.72 1.72a.75.75 0 1 0 1.06 1.06l3-3a.75.75 0 0 0 0-1.06l-3-3a.75.75 0 0 0-1.06 0Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        Log Out
                                    </div>
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="flex">
                <!-- Sidebar Navigation -->
                <aside
                    id="sidebar-navigation"
                    class="bg-primary text-white sticky top-12 h-[calc(100vh-3rem)] transition-all duration-300 ease-in-out overflow-y-auto md:block hidden"
                    :class="{
                        'w-48': isSidebarOpen,
                        'w-0': !isSidebarOpen,
                    }"
                    :aria-hidden="!isSidebarOpen"
                    aria-label="Sidebar navigation"
                >
                    <div
                        class="p-4 whitespace-nowrap transition-opacity duration-300"
                        :class="{
                            'opacity-0 pointer-events-none invisible':
                                !isSidebarOpen,
                            'opacity-100 visible': isSidebarOpen,
                        }"
                    >
                        <nav class="space-y-2">
                            <!-- Admin Navigation -->
                            <template v-if="isAdmin">
                                <Link
                                    :href="route('admin.dashboard')"
                                    :aria-current="
                                        $page.url.startsWith('/admin/dashboard')
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/admin/dashboard'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"
                                        />
                                        <path
                                            d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"
                                        />
                                    </svg>

                                    Overview
                                </Link>

                                <Link
                                    :href="route('admin.residents')"
                                    :aria-current="
                                        $page.url.startsWith('/admin/residents')
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/admin/residents'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z"
                                            clip-rule="evenodd"
                                        />
                                        <path
                                            d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"
                                        />
                                    </svg>

                                    Residents
                                </Link>

                                <Link
                                    :href="route('admin.volunteers')"
                                    :aria-current="
                                        $page.url.startsWith(
                                            '/admin/volunteers'
                                        )
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/admin/volunteers'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0 0 0 0-1.5h-2.25V7.5Z"
                                        />
                                    </svg>

                                    Volunteers
                                </Link>

                                <Link
                                    :href="route('admin.reports')"
                                    :aria-current="
                                        $page.url.startsWith('/admin/reports')
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/admin/reports'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>

                                    Reports
                                </Link>
                            </template>

                            <!-- Volunteer/Resident Navigation -->
                            <template v-else>
                                <Link
                                    :href="route('platform.home')"
                                    :aria-current="
                                        $page.url.startsWith('/platform/home')
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/platform/home'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"
                                        />
                                        <path
                                            d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"
                                        />
                                    </svg>

                                    Home
                                </Link>

                                <Link
                                    :href="route('platform.discover')"
                                    :aria-current="
                                        $page.url.startsWith(
                                            '/platform/discover'
                                        )
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/platform/discover'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    Discover
                                </Link>

                                <Link
                                    :href="route('platform.write')"
                                    :aria-current="
                                        $page.url.startsWith('/platform/write')
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/platform/write'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                                        />
                                        <path
                                            d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                                        />
                                    </svg>
                                    Write
                                </Link>

                                <Link
                                    :href="route('profile.show')"
                                    :aria-current="
                                        $page.url.startsWith('/user/profile')
                                            ? 'page'
                                            : undefined
                                    "
                                    class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                                    :class="{
                                        'bg-pressed text-black':
                                            $page.url.startsWith(
                                                '/user/profile'
                                            ),
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-6 mr-3"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>

                                    Profile
                                </Link>
                            </template>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <main
                    id="main-content"
                    class="flex-1 bg-background min-h-screen pb-16 lg:pb-0"
                >
                    <slot />
                </main>
            </div>

            <!-- Mobile Bottom Navigation (hidden on md+ screens) -->
            <nav
                class="md:hidden fixed bottom-0 left-0 right-0 bg-primary text-white border-t border-gray-600"
                role="navigation"
                aria-label="Mobile bottom navigation"
            >
                <div class="flex justify-around items-center py-2">
                    <!-- Admin Navigation -->
                    <template v-if="isAdmin">
                        <!-- Overview -->
                        <Link
                            :href="route('admin.dashboard')"
                            :aria-current="
                                $page.url.startsWith('/admin/dashboard')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/admin/dashboard'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"
                                />
                                <path
                                    d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"
                                />
                            </svg>
                            <span class="text-xs font-medium">Overview</span>
                        </Link>

                        <!-- Residents -->
                        <Link
                            :href="route('admin.residents')"
                            :aria-current="
                                $page.url.startsWith('/admin/residents')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/admin/residents'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z"
                                    clip-rule="evenodd"
                                />
                                <path
                                    d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"
                                />
                            </svg>
                            <span class="text-xs font-medium">Residents</span>
                        </Link>

                        <!-- Volunteers -->
                        <Link
                            :href="route('admin.volunteers')"
                            :aria-current="
                                $page.url.startsWith('/admin/volunteers')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/admin/volunteers'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0 0 0 0-1.5h-2.25V7.5Z"
                                />
                            </svg>
                            <span class="text-xs font-medium">Volunteers</span>
                        </Link>

                        <!-- Reports -->
                        <Link
                            :href="route('admin.reports')"
                            :aria-current="
                                $page.url.startsWith('/admin/reports')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/admin/reports'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="text-xs font-medium">Reports</span>
                        </Link>
                    </template>

                    <!-- Volunteer/Resident Navigation -->
                    <template v-else>
                        <!-- Home -->
                        <Link
                            :href="route('platform.home')"
                            :aria-current="
                                $page.url.startsWith('/platform/home')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/platform/home'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"
                                />
                                <path
                                    d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"
                                />
                            </svg>
                            <span class="text-xs font-medium">Home</span>
                        </Link>

                        <!-- Discover -->
                        <Link
                            :href="route('platform.discover')"
                            :aria-current="
                                $page.url.startsWith('/platform/discover')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/platform/discover'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="text-xs font-medium">Discover</span>
                        </Link>

                        <!-- Write -->
                        <Link
                            :href="route('platform.write')"
                            :aria-current="
                                $page.url.startsWith('/platform/write')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/platform/write'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                                />
                                <path
                                    d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                                />
                            </svg>
                            <span class="text-xs font-medium">Write</span>
                        </Link>

                        <!-- Profile -->
                        <Link
                            :href="route('profile.show')"
                            :aria-current="
                                $page.url.startsWith('/user/profile')
                                    ? 'page'
                                    : undefined
                            "
                            class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary"
                            :class="{
                                'bg-pressed text-black':
                                    $page.url.startsWith('/user/profile'),
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 mb-1"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="text-xs font-medium">Profile</span>
                        </Link>
                    </template>
                </div>
            </nav>
        </div>
    </div>
</template>
