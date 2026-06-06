import { usePage } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';

type RolePageProps = {
    auth?: {
        user?: {
            role?: {
                slug?: string | null;
                nama?: string | null;
            } | null;
        } | null;
    };
};

export type RolePrefix = '/admin' | '/kaprodi' | '/dekan';

export function useRolePrefix(): {
    rolePrefix: ComputedRef<RolePrefix>;
    roleSlug: ComputedRef<string>;
} {
    const page = usePage<RolePageProps>();

    const roleSlug = computed(() => {
        const slug = String(page.props.auth?.user?.role?.slug ?? '')
            .trim()
            .toLowerCase();

        if (slug !== '') {
            return slug;
        }

        return String(page.props.auth?.user?.role?.nama ?? '')
            .trim()
            .toLowerCase();
    });

    const rolePrefix = computed<RolePrefix>(() => {
        if (roleSlug.value.includes('kaprodi')) {
            return '/kaprodi';
        }

        if (roleSlug.value.includes('dekan')) {
            return '/dekan';
        }

        return '/admin';
    });

    return { rolePrefix, roleSlug };
}
