<template>
  <header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">{{ title }}</a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNavDropdown"
          aria-controls="navbarNavDropdown"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav" v-if="$store.state.token">
            <li class="nav-item">
              <a class="nav-link" href="#">{{ $t('pack.submit') }}</a>
            </li>
            <li class="nav-item dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                id="navbarDropdownTags"
                role="button"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                {{ $t('tag.name') }}
              </a>
            </li>
            <li class="nav-item"><a class="nav-link" href="#">{{ $t('tag.add') }}</a></li>
            <li class="nav-item"><a class="nav-link"
              href="#">{{ $t('picture.random') }}</a></li>
          </ul>
          <ul class="navbar-nav" v-if="$store.state.token">
            <li class="nav-item"><a class="nav-link" href="#">{{ $t('account.title') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="#">{{ $t('logout.title') }}</a></li>
          </ul>
          <ul class="navbar-nav" v-else>
            <li class="nav-item">
              <router-link class="nav-link" :to="{name: 'Login'}">
                {{ $t('login.title') }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link :to="{ name: 'Register' }" class="nav-link">
                {{ $t('register.title') }}
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
export default {
  name: 'HeaderNav',
  data() {
    return {
      title: process.env.VUE_APP_TITLE,
    };
  },
};
</script>

<style scoped>

</style>
