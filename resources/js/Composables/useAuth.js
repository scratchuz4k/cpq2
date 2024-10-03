
import { usePage } from "@inertiajs/vue3";


const page = usePage();

export const can = (permission) => {
    if (page.props.auth.roles.find(x => x == 'Super Admin')) {
        return true
    }
    if (page.props.auth.permissions.find(x => x == permission)) {
        return true
    }
    return false
}

export const hasRole = (role) => {
    if (page.props.auth.roles.find(x => x == role)) {
        return true
    }
    return false
}
