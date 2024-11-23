import { toast } from 'vue3-toastify'

export default function useToast() {
    function showSuccess(message: string) {
        toast.success(message, {
            position: toast.POSITION.TOP_RIGHT,
        })
    }

    function showInfo(message: string) {
        toast.info(message, {
            position: toast.POSITION.TOP_RIGHT,
        })
    }

    function showWarning(message: string) {
        toast.warning(message, {
            position: toast.POSITION.TOP_RIGHT,
        })
    }

    function showError(message: string) {
        toast.info(message, {
            position: toast.POSITION.TOP_RIGHT,
        })
    }

    return {
        showSuccess,
        showInfo,
        showWarning,
        showError,
    }
}
