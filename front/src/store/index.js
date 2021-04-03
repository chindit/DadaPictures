import { createStore } from 'vuex';
import { v1 as uuidv1 } from 'uuid';
import { i18n } from '@/locales';
import Api from '../Api';

export default createStore({
  state: {
    token: null,
    flashes: [],
  },
  getters: {
    flashes(state) {
      return state.flashes;
    },
  },
  mutations: {
    addFlash(state, payload) {
      state.flashes.push({ id: uuidv1(), ...payload });
    },
    deleteFlash(state, id) {
      state.flashes = state.flashes.filter((flash) => flash.id !== id);
    },
    login(state, payload) {
      state.token = payload;
      localStorage.setItem('token', payload);
    },
  },
  actions: {
    async login(context, payload) {
      try {
        const response = await Api.post(
          '/login_check',
          payload,
        );
        context.commit('login', response.data.token);
      } catch (e) {
        const message = e.response ? e.response.data.message : i18n.global.t('global.error');
        context.commit('addFlash', { level: 'danger', message });
      }
    },
    async register(context, payload) {
      try {
        await Api.post(
          '/register',
          payload,
        );
        return true;
      } catch (e) {
        context.commit('addFlash', { level: 'danger', message: e.response ? e.response.data.message : i18n.global.t('global.error') });
        return false;
      }
    },
  },
  modules: {
  },
});
