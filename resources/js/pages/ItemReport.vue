<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';

defineOptions({
    layout: ApplicationLayout
});

defineProps({
    items: {
        type: Object,
        required: true
    }
});

const downloadSpreadsheet = () => {
    window.location.href = route('items.exports.xlsx');
};

const handlePageChange = (page) => {
    router.get(route('reports.items'), { page }, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Laporan Barang"></Head>
    <v-container>
        <v-row>
            <v-col>
                <PageTitleHighlightPart first-part-title="Laporan" second-part-title="Barang"/>
            </v-col>
        </v-row>
        <v-row>
            <v-col class="flex justify-end">
                <v-btn 
                    variant="tonal" 
                    color="blue-darken-2"
                    @click="downloadSpreadsheet">
                    <v-icon icon="mdi-download" class="mr-2" />
                    Unduh Spreadsheet
                </v-btn>
            </v-col>
        </v-row>
        
        <v-row>
            <v-col>
                <v-data-table 
                    :headers="[
                        { title: 'No', key: 'no', width: '6%' },
                        { title: 'Nama Barang', key: 'name' },
                        { title: 'Nama Spesifikasi', key: 'specification' },
                        { title: 'Stok', key: 'stock', width: '10%' },
                        { title: 'Ukuran Satuan', key: 'unit', width: '12%' },
                        { title: 'Harga Satuan', key: 'price', width: '13%' },
                        { title: 'Terakhir Diperbarui', key: 'updated_at', width: '15%' }
                    ]" 
                    :items="items.data.map((item, index) => ({ 
                        no: (items.current_page - 1) * items.per_page + index + 1,
                        name: item.name,
                        specification: item.spesification_name || '-',
                        stock: item.stock,
                        unit: item.unit.name,
                        price: new Intl.NumberFormat('id-ID', { 
                            style: 'currency', 
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(item.price),
                        updated_at: new Date(item.updated_at).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        })
                    }))"
                    :items-per-page="items.per_page"
                    hide-default-footer
                    class="hidden! md:block!">
                    <template #headers="{ headers }">
                        <tr class="bg-blue-darken-2">
                            <th v-for="i in (headers.at(0).length)" :key="i">{{ headers.at(0).at(i - 1).title }}</th>
                        </tr>
                    </template>
                </v-data-table>
                
                <!-- Pagination -->
                <v-row class="mt-4">
                    <v-col class="flex justify-center">
                        <v-pagination
                            :length="items.last_page"
                            :model-value="items.current_page"
                            @update:model-value="handlePageChange"
                            color="blue-darken-2"
                            :total-visible="7"
                        ></v-pagination>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>