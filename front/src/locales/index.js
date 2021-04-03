import { createI18n } from 'vue-i18n';
import en from './en.json';
// import en from './fr.json';

export const defaultLocale = process.env.VUE_APP_I18N_LOCALE || 'en';

export const languages = {
  en,
  // fr,
};

export const i18n = createI18n({
  locale: 'en',
  messages: languages,
});
