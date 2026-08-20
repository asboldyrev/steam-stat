export function useArtwork() {
    const assetUrl = (artwork, type) => {
        const asset = artwork?.[type]
        if (!asset || typeof asset !== 'object') return null

        if (typeof asset.local_url === 'string' && asset.local_url.length > 0) {
            return asset.local_url
        }

        if (typeof asset.url === 'string' && asset.url.length > 0) {
            return asset.url
        }

        return null
    }

    const firstAssetUrl = (artwork, types = []) => {
        for (const type of types) {
            const url = assetUrl(artwork, type)
            if (url) return url
        }

        return null
    }

    return {
        assetUrl,
        firstAssetUrl,
    }
}
