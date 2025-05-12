<script setup>
import { useLayout } from '@/layout/composables/layout';
import { onBeforeMount, ref, watch, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();
const { layoutState, setActiveMenuItem, toggleMenu } = useLayout();

const isAuthenticated = computed(() => {
  return page.props.auth && page.props.auth.user;
});

const authUser = computed(() => {
  return isAuthenticated.value ? page.props.auth.user : null;
});

const props = defineProps({
  item: {
    type: Object,
    default: () => ({})
  },
  index: {
    type: Number,
    default: 0
  },
  root: {
    type: Boolean,
    default: true
  },
  parentItemKey: {
    type: String,
    default: null
  }
});

const isActiveMenu = ref(false);
const itemKey = ref(null);

onBeforeMount(() => {
  itemKey.value = props.parentItemKey ? props.parentItemKey + '-' + props.index : String(props.index);
  const activeItem = layoutState.activeMenuItem;
  isActiveMenu.value = activeItem === itemKey.value || activeItem ? activeItem.startsWith(itemKey.value + '-') : false;
});

watch(
  () => layoutState.activeMenuItem,
  (newVal) => {
    isActiveMenu.value = newVal === itemKey.value || newVal.startsWith(itemKey.value + '-');
  }
);

function itemClick(event, item) {
  if (item.requiresAuth && !isAuthenticated.value) {
    event.preventDefault();
    window.location.href = route('login');
    return;
  }

  if (item.permissions && authUser.value) {
    const hasPermission = item.permissions.some(permission =>
      authUser.value.permissions && authUser.value.permissions.includes(permission)
    );

    if (!hasPermission) {
      event.preventDefault();
      return;
    }
  }

  if (item.disabled) {
    event.preventDefault();
    return;
  }

  if ((item.to || item.url) && (layoutState.staticMenuMobileActive || layoutState.overlayMenuActive)) {
    toggleMenu();
  }

  if (item.command) {
    item.command({ originalEvent: event, item: item });
  }

  const foundItemKey = item.items ? (isActiveMenu.value ? props.parentItemKey : itemKey) : itemKey.value;
  setActiveMenuItem(foundItemKey);
}

function checkActiveRoute(item) {
  // Comprobar si la ruta actual coincide con la ruta del elemento
  return page.url === item.to;
}

// Verificar si el elemento debe mostrarse basado en la autenticación y permisos
function shouldShowItem(item) {
  // Si el elemento tiene requiresAuth y el usuario no está autenticado, no mostrar
  if (item.requiresAuth && !isAuthenticated.value) {
    return false;
  }

  // Si el elemento tiene guestOnly y el usuario está autenticado, no mostrar
  if (item.guestOnly && isAuthenticated.value) {
    return false;
  }

  // Verificar permisos si es necesario
  if (item.permissions && authUser.value) {
    const hasPermission = item.permissions.some(permission =>
      authUser.value.permissions && authUser.value.permissions.includes(permission)
    );

    if (!hasPermission) {
      return false;
    }
  }

  // Verificar roles si es necesario
  if (item.roles && authUser.value) {
    const hasRole = item.roles.some(role =>
      authUser.value.roles && authUser.value.roles.includes(role)
    );

    if (!hasRole) {
      return false;
    }
  }

  return item.visible !== false;
}
</script>

<template>
  <li :class="{ 'layout-root-menuitem': root, 'active-menuitem': isActiveMenu }">
    <div v-if="root && shouldShowItem(item)" class="layout-menuitem-root-text">{{ item.label }}</div>

    <!-- Para enlaces externos o elementos con submenús -->
    <a v-if="(!item.to || item.items) && shouldShowItem(item)" :href="item.url" @click="itemClick($event, item, index)"
      :class="item.class" :target="item.target" tabindex="0">
      <i :class="item.icon" class="layout-menuitem-icon"></i>
      <span class="layout-menuitem-text">{{ item.label }}</span>
      <i class="pi pi-fw pi-angle-down layout-submenu-toggler" v-if="item.items"></i>
    </a>

    <!-- Para rutas internas usando Inertia Link -->
    <Link v-if="item.to && !item.items && shouldShowItem(item)" :href="item.to" @click="itemClick($event, item, index)"
      :class="[item.class, { 'active-route': checkActiveRoute(item) }]" tabindex="0" :only="item.only || []"
      :preserve-state="item.preserveState || false" :preserve-scroll="item.preserveScroll || false">
    <i :class="item.icon" class="layout-menuitem-icon"></i>
    <span class="layout-menuitem-text">{{ item.label }}</span>
    </Link>

    <!-- Submenú recursivo -->
    <Transition v-if="item.items && shouldShowItem(item)" name="layout-submenu">
      <ul v-show="root ? true : isActiveMenu" class="layout-submenu">
        <app-menu-item v-for="(child, i) in item.items.filter(childItem => shouldShowItem(childItem))" :key="i"
          :index="i" :item="child" :parentItemKey="itemKey" :root="false"></app-menu-item>
      </ul>
    </Transition>
  </li>
</template>

<style lang="scss" scoped></style>