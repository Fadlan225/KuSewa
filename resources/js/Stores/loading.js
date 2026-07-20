import { reactive } from 'vue';

export const loadingState = reactive({
    show: false,
});

export function showLoading() {
    loadingState.show = true;
}

export function hideLoading() {
    loadingState.show = false;
}
