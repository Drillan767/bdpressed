import { useTheme } from 'vuetify'

export default function useGradientGenerator() {
    // Call useTheme at the top level of the composable
    const theme = useTheme()

    const { primary, secondary } = theme.current.value.colors

    function generateColors(
        steps: number,
        startColor: string = primary,
        endColor: string = secondary,
    ): string[] {
        if (steps < 2) {
            throw new Error('Steps must be at least 2')
        }

        // Rest of your code remains the same...
        if (steps === 2) {
            return [startColor, endColor]
        }

        const hexToRgb = (hex: string): [number, number, number] => {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
            if (!result) {
                throw new Error(`Invalid hex color: ${hex}`)
            }
            return [
                Number.parseInt(result[1], 16),
                Number.parseInt(result[2], 16),
                Number.parseInt(result[3], 16),
            ]
        }

        const rgbToHex = (r: number, g: number, b: number): string => {
            const componentToHex = (c: number): string => {
                const hex = Math.round(c).toString(16)
                return hex.length === 1 ? `0${hex}` : hex
            }
            return `#${componentToHex(r)}${componentToHex(g)}${componentToHex(b)}`
        }

        const [startR, startG, startB] = hexToRgb(startColor)
        const [endR, endG, endB] = hexToRgb(endColor)
        const colors: string[] = []

        for (let i = 0; i < steps; i++) {
            const ratio = i / (steps - 1)
            const r = startR + (endR - startR) * ratio
            const g = startG + (endG - startG) * ratio
            const b = startB + (endB - startB) * ratio
            colors.push(rgbToHex(r, g, b))
        }

        return colors
    }

    return {
        generateColors,
    }
}
