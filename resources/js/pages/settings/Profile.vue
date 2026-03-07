<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { useInitials } from '@/composables/useInitials';
import { useUploadThing } from '@/composables/useUploadThing';
import { Camera, Trash2 } from 'lucide-vue-next';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;
const { getInitials } = useInitials();

const form = useForm({
    name: user.name,
    email: user.email,
});

// Avatar upload handling
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);

// Use UploadThing composable
const {
    isUploading: isUploadingAvatar,
    uploadError: avatarError,
    upload: uploadToUploadThing,
} = useUploadThing({
    maxFileSize: 2,
    allowedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'],
    onSuccess: (result) => {
        // Save the URL to backend
        router.post(route('profile.avatar.update'), {
            avatar_url: result.url,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                avatarPreview.value = null;
            },
            onError: (errors: any) => {
                avatarError.value = errors.avatar_url || 'Failed to save avatar URL';
                avatarPreview.value = null;
            },
        });
    },
    onError: (error) => {
        console.error('Upload error:', error);
        avatarPreview.value = null;
    },
});

const currentAvatar = computed(() => avatarPreview.value || user.avatar);
const hasAvatar = computed(() => currentAvatar.value && currentAvatar.value !== '');

const triggerAvatarInput = () => {
    avatarInput.value?.click();
};

const handleAvatarChange = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
        avatarPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    // Upload avatar to UploadThing
    await uploadToUploadThing(file);
};

const deleteAvatar = () => {
    if (!confirm('Are you sure to delete profile picture ?')) return;

    router.delete(route('profile.avatar.delete'), {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;
        },
    });
};

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :errors="{}" :breadcrumbs="breadcrumbItems">

        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <!-- Avatar Upload Section -->
                <div class="grid gap-2">
                    <Label>Profile picture</Label>
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <Avatar class="h-20 w-20 rounded-full ring-2 ring-offset-2 ring-muted">
                                <AvatarImage v-if="hasAvatar" :src="currentAvatar!" :alt="user.name" />
                                <AvatarFallback class="rounded-full text-lg font-semibold">
                                    {{ getInitials(user.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <!-- Overlay for changing avatar -->
                            <button type="button" @click="triggerAvatarInput"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-full cursor-pointer"
                                :disabled="isUploadingAvatar">
                                <Camera class="h-6 w-6 text-white" />
                            </button>
                            <!-- Loading indicator -->
                            <div v-if="isUploadingAvatar"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full">
                                <div
                                    class="h-6 w-6 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="flex gap-2">
                                <Button type="button" variant="outline" size="sm" @click="triggerAvatarInput"
                                    :disabled="isUploadingAvatar">
                                    <Camera class="h-4 w-4 mr-2" />
                                    Edit
                                </Button>
                                <Button v-if="hasAvatar && !avatarPreview" type="button" variant="outline" size="sm"
                                    @click="deleteAvatar" class="text-destructive hover:text-destructive">
                                    <Trash2 class="h-4 w-4 mr-2" />
                                    Delete
                                </Button>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                JPG, PNG, GIF, atau WebP. Max 2MB.
                            </p>
                        </div>
                        <!-- Hidden file input -->
                        <input ref="avatarInput" type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="hidden"
                            @change="handleAvatarChange" />
                    </div>
                    <p v-if="avatarError" class="text-sm text-destructive">{{ avatarError }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name"
                            placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                            autocomplete="username" placeholder="Email address" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link :href="route('verification.send')" method="post" as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
