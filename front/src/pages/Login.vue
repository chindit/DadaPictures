<template>
  <div class="d-flex container h-100 justify-content-center">
    <div class="d-flex justify-content-center align-middle h-100">
      <div class="user_card">
        <div class="d-flex justify-content-center">
          <div class="brand_logo_container">
            <img src="@/assets/icon.png" class="brand_logo" alt="Logo">
          </div>
        </div>
        <div class="d-flex justify-content-center flex-column form_container">
          <flash v-for="message in $store.getters.flashes" :message="message" :key="message.id" />
          <form method="post" @submit.prevent="submitLogin()">
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input
                type="text"
                class="form-control"
                aria-label="Username"
                name="username"
                :placeholder="$t('login.username')"
                v-model="username"
                required
                minlength="3"
              >
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
              <input
                type="password"
                class="form-control"
                aria-label="Password"
                name="password"
                v-model="password"
                :placeholder="$t('login.password')"
                required
                minlength="6"
              >
            </div>
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="customControlInline"
                  name="_remember_me" v-model="rememberMe">
                <label class="custom-control-label ml-2" for="customControlInline">
                  {{ $t('login.remember') }}</label>
              </div>
            </div>
            <div class="d-flex justify-content-center mt-3 login_container">
              <input type="submit" class="btn login_btn" :value="$t('login.login')">
            </div>
          </form>
        </div>

        <div class="mt-4">
          <div class="d-flex justify-content-center links">
            {{  $t('login.noAccount') }}&nbsp;&nbsp;
            <router-link :to="{ name: 'Register' }" class="ml-2">
              {{ $t('login.signUp') }}
            </router-link>
          </div>
          <!--<div class="d-flex justify-content-center links">
          <a href="#">Forgot your password?</a>
        </div>-->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Flash from '@/components/Flash.vue';
import { mapActions } from 'vuex';

export default {
  name: 'Login',
  components: {
    Flash,
  },
  data() {
    return {
      username: '',
      password: '',
      rememberMe: false,
    };
  },
  methods: {
    ...mapActions([
      'login',
    ]),
    async submitLogin() {
      await this.login({
        username: this.username,
        password: this.password,
        remember: this.rememberMe,
      });
      if (this.$store.state.token) {
        this.$router.push({ name: 'Home' });
      }
    },
  },
};
</script>

<style lang="sass" scoped>
.user_card
  width: 350px
  margin-top: auto
  margin-bottom: auto
  /*background: #f39c12;*/
  position: relative
  display: flex
  justify-content: center
  flex-direction: column
  padding: 10px
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19)
  -webkit-box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19)
  -moz-box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19)
  border-radius: 5px

.brand_logo_container
  position: absolute
  height: 170px
  width: 170px
  top: -75px
  border-radius: 50%
  background: #60a3bc
  padding: 10px
  text-align: center

.brand_logo
  height: 150px
  width: 150px
  border-radius: 50%
  border: 2px solid white

.form_container
  margin-top: 100px

.login_btn
  width: 100%
  background: #0070B8 !important
  color: white !important

.login_btn:focus
  box-shadow: none !important
  outline: 0 !important

.login_container
  padding: 0 2rem

.input-group-text
  background: #0070B8 !important
  color: white !important
  border: 0 !important
  border-radius: 0.25rem 0 0 0.25rem !important

.input_user, .input_pass:focus
  box-shadow: none !important
  outline: 0 !important

.custom-checkbox .custom-control-input:checked ~ .custom-control-label::before
  background-color: #c0392b !important

.invalid-feedback
  display: block

</style>
