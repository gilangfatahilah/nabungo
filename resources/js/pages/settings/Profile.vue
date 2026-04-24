<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';

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
const user = computed(() => page.props.auth.user as User);
const { getInitials } = useInitials();

const form = useForm({
    name: user.value.name,
    email: user.value.email,
});

const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);
const avatarUploading = ref(false);
const avatarError = ref<string | null>(null);
const confirmingDelete = ref(false);
const avatarDeleting = ref(false);

const currentAvatar = computed(() => avatarPreview.value || user.value.avatar);
const hasAvatar = computed(() => !!currentAvatar.value);

const triggerAvatarInput = () => {
    avatarInput.value?.click();
};

const handleAvatarChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    // Show local preview immediately
    const reader = new FileReader();
    reader.onload = (e) => { avatarPreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);

    // Upload to backend → Cloudinary
    avatarError.value = null;
    avatarUploading.value = true;

    const toastId = toast.loading('Uploading avatar...', {
        description: 'Please wait while your photo is being uploaded.',
    });

    const formData = new FormData();
    formData.append('avatar', file);

    router.post(route('profile.avatar.update'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (response) => {
            avatarPreview.value = null;
            toast.success('Avatar updated!', {
                id: toastId,
                description: 'Your profile photo has been saved.',
            });
        },
        onError: (errors) => {
            avatarPreview.value = null;
            const message = errors.avatar ?? 'Failed to upload avatar.';
            avatarError.value = message;
            toast.error('Upload failed', {
                id: toastId,
                description: message,
            });
        },
        onFinish: () => {
            avatarUploading.value = false;
            if (avatarInput.value) avatarInput.value.value = '';
        },
    });
};

const confirmDeleteAvatar = () => {
    confirmingDelete.value = true;
};

const cancelDeleteAvatar = () => {
    confirmingDelete.value = false;
};

const deleteAvatar = () => {
    confirmingDelete.value = false;
    avatarPreview.value = null;
    avatarError.value = null;
    avatarDeleting.value = true;

    const toastId = toast.loading('Deleting avatar...', {
        description: 'Removing your profile photo.',
    });

    router.delete(route('profile.avatar.delete'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Avatar removed', {
                id: toastId,
                description: 'Your profile photo has been deleted.',
            });
        },
        onError: () => {
            toast.error('Delete failed', {
                id: toastId,
                description: 'Something went wrong. Please try again.',
            });
        },
        onFinish: () => {
            avatarDeleting.value = false;
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
                            <Avatar class="h-20 w-20 rounded-full ring-2 ring-offset-2 ring-muted"
                                :class="{ 'opacity-60': avatarDeleting }">
                                <AvatarImage v-if="hasAvatar" :src="currentAvatar!" :alt="user.name" />
                                <AvatarFallback class="rounded-full text-lg font-semibold">
                                    {{ getInitials(user.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <!-- Overlay: uploading spinner -->
                            <div v-if="avatarUploading || avatarDeleting"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full">
                                <span class="h-5 w-5 rounded-full border-2 border-white border-t-transparent animate-spin" />
                            </div>
                            <!-- Overlay: hover to change (idle only) -->
                            <button v-else type="button" @click="triggerAvatarInput"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-full cursor-pointer">
                                <Camera class="h-6 w-6 text-white" />
                            </button>
                        </div>

                        <div class="flex flex-col gap-2">
                            <!-- Normal actions -->
                            <div v-if="!confirmingDelete" class="flex gap-2">
                                <Button type="button" variant="outline" size="sm"
                                    @click="triggerAvatarInput"
                                    :disabled="avatarUploading || avatarDeleting">
                                    <Camera class="h-4 w-4 mr-2" />
                                    {{ avatarUploading ? 'Uploading...' : 'Edit' }}
                                </Button>
                                <Button v-if="hasAvatar" type="button" variant="outline" size="sm"
                                    @click="confirmDeleteAvatar"
                                    :disabled="avatarUploading || avatarDeleting"
                                    class="text-destructive hover:text-destructive">
                                    <Trash2 class="h-4 w-4 mr-2" />
                                    {{ avatarDeleting ? 'Deleting...' : 'Delete' }}
                                </Button>
                            </div>

                            <!-- Delete confirmation -->
                            <div v-else class="flex flex-col gap-2">
                                <p class="text-xs text-muted-foreground">Remove your profile photo?</p>
                                <div class="flex gap-2">
                                    <Button type="button" size="sm" variant="destructive" @click="deleteAvatar">
                                        Yes, delete
                                    </Button>
                                    <Button type="button" size="sm" variant="outline" @click="cancelDeleteAvatar">
                                        Cancel
                                    </Button>
                                </div>
                            </div>

                            <p class="text-xs text-muted-foreground">
                                JPG, PNG, GIF, atau WebP. Max 2MB.
                            </p>
                            <p v-if="avatarError" class="text-xs text-destructive">{{ avatarError }}</p>
                        </div>

                        <!-- Hidden file input -->
                        <input ref="avatarInput" type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="hidden"
                            @change="handleAvatarChange" />
                    </div>
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
