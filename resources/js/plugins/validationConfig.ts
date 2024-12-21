export default function validationConfig(state: any) {
    return {
        props: {
            'error-messages': state.errors,
        },
    }
}
