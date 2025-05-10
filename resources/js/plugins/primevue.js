import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import Tooltip from "primevue/tooltip";
import Aura from '@primeuix/themes/aura';
import StyleClass from 'primevue/styleclass';
import Toast from 'primevue/toast';
export default (app) => {
    // Usar PrimeVue y sus servicios
    app.use(PrimeVue, {
        theme: {
            preset: Aura,
            options: {
                darkModeSelector: ".app-dark",
            },
        },
    });
    app.use(ConfirmationService);
    app.use(ToastService);
    app.directive('styleclass', StyleClass);
    app.directive("tooltip", Tooltip);
    app.component('Toast', Toast);
};
