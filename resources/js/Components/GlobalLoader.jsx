import { useEffect, useState } from 'react'
import { router } from '@inertiajs/react'

export default function GlobalLoader() {
    const [loading, setLoading] = useState(false)

    useEffect(() => {
        const start = () => setLoading(true)
        const finish = () => setLoading(false)

        const offStart = router.on('start', start)
        const offFinish = router.on('finish', finish)
        const offError = router.on('error', finish)

        return () => {
            if (typeof offStart === 'function') offStart()
            if (typeof offFinish === 'function') offFinish()
            if (typeof offError === 'function') offError()
        }
    }, [])

    if (!loading) return null

    return (
        <div
            style={{
                position: 'fixed',
                right: 20,
                bottom: 20,
                zIndex: 9999,
                background: 'rgba(255,255,255,0.9)',
                padding: '10px 14px',
                borderRadius: 6,
                boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
                display: 'flex',
                alignItems: 'center',
                gap: 8,
            }}
        >
            <i className="fa fa-spinner fa-spin"></i>
            <span style={{ fontSize: 12 }}>Loading…</span>
        </div>
    )
}
