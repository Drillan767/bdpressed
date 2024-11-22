import { toast } from 'vue3-toastify'

export default function useToast() {
    function showSucess(message: string) {
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
        showSucess,
        showInfo,
        showWarning,
        showError,
    }
}
