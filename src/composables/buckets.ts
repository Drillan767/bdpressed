import { remove, uploadData } from 'aws-amplify/storage'

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
        storeFiles,
        storeSingleFile,
        deleteFiles,
    }
}