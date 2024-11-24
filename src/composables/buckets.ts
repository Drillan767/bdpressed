import { getUrl, remove, uploadData } from 'aws-amplify/storage'

export default function useBuckets() {
    async function storeSingleFile(file: File, path: string) {
        try {
            const result = await uploadData({
                data: file,
                path: `${path}/${file.name}`,
            })
                .result

            return result?.path
        }
        catch (error) {
            console.error(error)
        }
    }

    function storeFiles(files: File[], path: string) {
        try {
            const promises = files.map(async (file) => {
                const result = await uploadData({
                    data: file,
                    path: `${path}/${file.name}`,
                })
                    .result

                return result?.path
            })

            return Promise.all(promises)
        }
        catch (error) {
            console.error(error)
        }
    }

    async function getSingleItem(path: string) {
        const result = await getUrl({
            path,
        })

        return result.url.href
    }

    async function getItems(paths: string[]) {
        const promises = paths.map(async (path) => {
            const result = await getUrl({
                path,
            })

            return result.url.href
        })

        return Promise.all(promises)
    }

    async function deleteFiles(files: string[]) {
        try {
            for (const file of files) {
                await remove({
                    path: file,
                })
            }
        }
        catch (error) {
            console.error(error)
        }
    }

    return {
        getSingleItem,
        getItems,
        storeFiles,
        storeSingleFile,
        deleteFiles,
    }
}
