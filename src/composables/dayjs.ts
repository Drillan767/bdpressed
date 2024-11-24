import dayjs from 'dayjs'
import 'dayjs/locale/fr'

dayjs.locale('fr')

export default function useDayjs() {
    return {
        dayjs,
    }
}
