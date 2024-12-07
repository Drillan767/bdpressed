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

    function toSlug(text: string) {
        return text
            .replace(/^\s+|\s+$/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
    }

    return {
        toParagraphs,
        toSlug,
    }
}
