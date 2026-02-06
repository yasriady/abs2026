import { usePage } from '@inertiajs/react'

export default function can(permission) {
    const { auth } = usePage().props
    return auth?.user?.permissions?.includes(permission)
}
