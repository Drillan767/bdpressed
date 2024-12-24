export default function useStrings() {
    function toParagraphs(text: string) {
        return text
            .split('\n')
            .reduce((acc, current) => {
                if (current.trim().length === 0)
                    return acc
                return `${acc}<p>${current}</p>`
            }, '')
    }

    return {
        toParagraphs,
    }
}
