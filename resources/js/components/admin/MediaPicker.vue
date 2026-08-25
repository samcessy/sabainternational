<script setup lang="ts">
import { FileText, ImageOff, X } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { picker } from '@/routes/admin/media';

type MediaOption = {
    id: number;
    filename: string;
    alt_text: string | null;
    thumbnail_url: string | null;
};

const props = withDefaults(
    defineProps<{
        name: string;
        label: string;
        chooseLabel?: string;
        initialMediaId?: number | null;
        initialPreviewUrl?: string | null;
        initialFileName?: string | null;
    }>(),
    {
        chooseLabel: 'Choose Image',
        initialMediaId: null,
        initialPreviewUrl: null,
        initialFileName: null,
    },
);

const open = ref(false);
const loading = ref(false);
const items = ref<MediaOption[]>([]);
const selectedId = ref<number | null>(props.initialMediaId);
const selectedPreviewUrl = ref<string | null>(props.initialPreviewUrl);
const selectedFileName = ref<string | null>(props.initialFileName);

async function openPicker() {
    open.value = true;

    if (items.value.length > 0) {
        return;
    }

    loading.value = true;

    try {
        const response = await fetch(picker.url(), {
            headers: { Accept: 'application/json' },
        });
        const body = await response.json();
        items.value = body.data;
    } finally {
        loading.value = false;
    }
}

function select(item: MediaOption) {
    selectedId.value = item.id;
    selectedPreviewUrl.value = item.thumbnail_url;
    selectedFileName.value = item.filename;
    open.value = false;
}

function clear() {
    selectedId.value = null;
    selectedPreviewUrl.value = null;
    selectedFileName.value = null;
}
</script>

<template>
    <div class="space-y-2">
        <Label>{{ label }}</Label>
        <input type="hidden" :name="name" :value="selectedId ?? ''" />
        <div class="flex items-center gap-3">
            <div
                class="flex size-20 shrink-0 flex-col items-center justify-center gap-1 overflow-hidden rounded-md border border-border bg-muted p-1 text-center"
            >
                <img
                    v-if="selectedPreviewUrl"
                    :src="selectedPreviewUrl"
                    alt=""
                    class="size-full object-cover"
                />
                <template v-else>
                    <FileText
                        v-if="selectedFileName"
                        class="size-5 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <ImageOff
                        v-else
                        class="size-5 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <span
                        v-if="selectedFileName"
                        class="line-clamp-2 text-[10px] break-all text-muted-foreground"
                    >
                        {{ selectedFileName }}
                    </span>
                </template>
            </div>
            <div class="flex flex-col gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="openPicker"
                >
                    {{ chooseLabel }}
                </Button>
                <Button
                    v-if="selectedId"
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="clear"
                >
                    <X class="size-4" aria-hidden="true" />
                    Remove
                </Button>
            </div>
        </div>
    </div>

    <Dialog :open="open" @update:open="(value) => (open = value)">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ chooseLabel }}</DialogTitle>
            </DialogHeader>
            <div
                v-if="loading"
                class="py-10 text-center text-sm text-muted-foreground"
            >
                Loading…
            </div>
            <div
                v-else-if="items.length === 0"
                class="py-10 text-center text-sm text-muted-foreground"
            >
                No files in the library yet.
            </div>
            <div
                v-else
                class="grid max-h-96 grid-cols-3 gap-3 overflow-y-auto sm:grid-cols-4"
            >
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="flex aspect-square flex-col items-center justify-center gap-1 overflow-hidden rounded-md border border-border p-1 text-center focus-visible:ring-2 focus-visible:ring-ring"
                    :aria-label="`Select ${item.alt_text ?? item.filename}`"
                    @click="select(item)"
                >
                    <img
                        v-if="item.thumbnail_url"
                        :src="item.thumbnail_url"
                        :alt="item.alt_text ?? ''"
                        class="size-full object-cover"
                    />
                    <template v-else>
                        <FileText
                            class="size-5 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <span
                            class="line-clamp-2 text-[10px] break-all text-muted-foreground"
                        >
                            {{ item.filename }}
                        </span>
                    </template>
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>
