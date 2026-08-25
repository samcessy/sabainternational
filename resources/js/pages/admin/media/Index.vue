<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ImageOff, Trash2, Upload } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { index, destroy as destroyMedia } from '@/routes/admin/media';
import { store as storeMedia } from '@/routes/media';
import type { Auth } from '@/types';

type Option = { value: string; label: string };

type MediaItem = {
    id: number;
    filename: string;
    alt_text: string | null;
    consent_status: string | null;
    consent_status_label: string | null;
    program: string | null;
    story: string | null;
    thumbnail_url: string | null;
    created_at: string | null;
};

type PaginatedMedia = {
    data: MediaItem[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    media: PaginatedMedia;
    imageConsentOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Media Library', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () => page.props.auth.permissions.includes('content:manage');

const pendingDelete = ref<MediaItem | null>(null);
const uploading = ref(false);
const uploadError = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const altText = ref('');
const consentStatus = ref('');

function readXsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function upload() {
    const file = fileInput.value?.files?.[0];

    if (!file) {
        uploadError.value = 'Please choose a file.';

        return;
    }

    uploadError.value = null;
    uploading.value = true;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('alt_text', altText.value);
    formData.append('consent_status', consentStatus.value);

    try {
        const response = await fetch(storeMedia.url(), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': readXsrfToken(),
            },
            credentials: 'same-origin',
            body: formData,
        });

        if (!response.ok) {
            const body = await response.json().catch(() => null);
            uploadError.value =
                body?.errors?.file?.[0] ??
                body?.errors?.alt_text?.[0] ??
                body?.errors?.consent_status?.[0] ??
                'Upload failed. Please try again.';

            return;
        }

        altText.value = '';
        consentStatus.value = '';

        if (fileInput.value) {
            fileInput.value.value = '';
        }

        router.reload({ only: ['media'] });
    } catch {
        uploadError.value =
            'Something went wrong. Please check your connection and try again.';
    } finally {
        uploading.value = false;
    }
}

function performDelete() {
    if (!pendingDelete.value) {
        return;
    }

    router.delete(destroyMedia.url(pendingDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            pendingDelete.value = null;
        },
    });
}

function decodeLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
}
</script>

<template>
    <Head title="Media Library" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">Media Library</h1>

        <div
            v-if="canManage()"
            class="mt-6 max-w-xl rounded-lg border border-border p-4"
        >
            <h2 class="text-sm font-medium text-foreground">Upload File</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Images (JPG, PNG, WebP) or PDF documents, up to 20MB.
            </p>
            <form class="mt-3 space-y-4" @submit.prevent="upload">
                <div class="space-y-2">
                    <Label for="file">File</Label>
                    <input
                        id="file"
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,application/pdf"
                        class="block w-full text-sm text-foreground file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium"
                        :aria-describedby="
                            uploadError ? 'upload-error' : undefined
                        "
                    />
                </div>
                <div class="space-y-2">
                    <Label for="alt_text">Alt Text (required for images)</Label>
                    <Input id="alt_text" v-model="altText" />
                </div>
                <div class="space-y-2">
                    <Label for="consent_status">
                        Consent Status (required for images)
                    </Label>
                    <Select v-model="consentStatus">
                        <SelectTrigger id="consent_status" class="w-full">
                            <SelectValue
                                placeholder="Choose a consent status"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in imageConsentOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <InputError
                    id="upload-error"
                    :message="uploadError ?? undefined"
                />
                <Button type="submit" variant="cta" :disabled="uploading">
                    <Spinner v-if="uploading" />
                    <Upload v-else class="size-4" aria-hidden="true" />
                    Upload
                </Button>
            </form>
        </div>

        <div v-if="media.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No media uploaded yet.</p>
        </div>

        <div
            v-else
            class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
        >
            <div
                v-for="item in media.data"
                :key="item.id"
                class="overflow-hidden rounded-lg border border-border"
            >
                <div
                    class="flex aspect-square items-center justify-center bg-muted"
                >
                    <img
                        v-if="item.thumbnail_url"
                        :src="item.thumbnail_url"
                        :alt="item.alt_text ?? ''"
                        class="size-full object-cover"
                    />
                    <div
                        v-else
                        class="flex flex-col items-center gap-1 text-muted-foreground"
                    >
                        <ImageOff class="size-6" aria-hidden="true" />
                        <span class="text-xs">No preview</span>
                    </div>
                </div>
                <div class="space-y-1 p-3 text-sm">
                    <p class="truncate font-medium text-foreground">
                        {{ item.alt_text ?? item.filename }}
                    </p>
                    <p
                        v-if="item.program || item.story"
                        class="truncate text-muted-foreground"
                    >
                        {{ item.program ?? item.story }}
                    </p>
                    <div v-if="canManage()" class="flex justify-end pt-1">
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            :aria-label="`Delete ${item.alt_text ?? item.filename}`"
                            @click="pendingDelete = item"
                        >
                            <Trash2 class="size-4" aria-hidden="true" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <nav
            v-if="media.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Media pagination"
        >
            <Link
                v-for="(link, linkIndex) in media.links"
                :key="linkIndex"
                :href="link.url ?? '#'"
                :class="[
                    'flex min-h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm',
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                    !link.url && 'pointer-events-none opacity-40',
                ]"
                :aria-current="link.active ? 'page' : undefined"
            >
                {{ decodeLabel(link.label) }}
            </Link>
        </nav>
    </div>

    <Dialog
        :open="pendingDelete !== null"
        @update:open="(open) => (pendingDelete = open ? pendingDelete : null)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete this file?</DialogTitle>
                <DialogDescription>
                    This will permanently delete "{{
                        pendingDelete?.alt_text ?? pendingDelete?.filename
                    }}" and any variants. This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="pendingDelete = null">
                    Cancel
                </Button>
                <Button variant="destructive" @click="performDelete">
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
