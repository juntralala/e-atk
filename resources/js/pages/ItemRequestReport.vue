<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import DatePicker from '@/components/molecules/DatePicker.vue';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    itemRequests: {
        type: Object,
        required: true
    },
    total: {
        type: Number,
        default: 0
    },
    filters: {
        type: Object,
        default: () => ({
            start: null,
            end: null,
            status: null
        })
    }
});

// Ambil query params dari URL
const urlParams = computed(() => new URLSearchParams(window.location.search));

const startDate = ref(
    urlParams.value.get('start')
        ? new Date(urlParams.value.get('start'))
        : new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
);

const endDate = ref(
    urlParams.value.get('end')
        ? new Date(urlParams.value.get('end'))
        : new Date()
);

const selectedStatus = ref(urlParams.value.get('status') || '');

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'pending', label: 'Pending' },
    { value: 'accepted', label: 'Diterima' },
    { value: 'rejected', label: 'Ditolak' }
];

const applyFilter = () => {
    router.get(route('items.requests.exports.view'), {
        start: startDate.value.toISOString(),
        end: endDate.value.toISOString(),
        status: selectedStatus.value,
        page: 1
    }, {
        preserveState: false,
        preserveScroll: false
    });
};

const downloadSpreadsheet = () => {
    window.location.href = route('items.requests.exports.xlsx', {
        start: startDate.value.toISOString(),
        end: endDate.value.toISOString(),
        status: selectedStatus.value
    });
};

const handlePageChange = (page) => {
    router.get(route('items.requests.exports.view'), {
        start: startDate.value.toISOString(),
        end: endDate.value.toISOString(),
        status: selectedStatus.value,
        page
    }, {
        preserveState: false,
        preserveScroll: true
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'blue-grey-lighten-4',
        accepted: 'green-accent-2',
        rejected: 'red-accent-2'
    };
    return colors[status] || 'grey';
};

const getStatusText = (status) => {
    const texts = {
        pending: 'Menunggu',
        accepted: 'Diterima',
        rejected: 'Ditolak'
    };
    return texts[status] || status;
};

// Flatten data untuk table
const tableData = props.itemRequests.data.flatMap((request, requestIndex) => {
    return request.item_request_details.map((detail, detailIndex) => {
        const globalIndex = props.itemRequests.data
            .slice(0, requestIndex)
            .reduce((sum, r) => sum + r.item_request_details.length, 0) + detailIndex;
        
        return {
            no: (props.itemRequests.current_page - 1) * props.itemRequests.per_page + globalIndex + 1,
            requestDate: request.request_date,
            requesterName: request.requester.name,
            itemName: detail.item.name,
            specificationName: detail.item.spesification_name,
            requestedQuantity: detail.requested_quantity,
            receivedQuantity: detail.received_quantity,
            unit: detail.item.unit.name,
            price: detail.price,
            status: request.status,
            responder: request.responder ? request.responder.name : '-',
            responseDate: request.response_date
        };
    });
});
</script>

<template>
    <Head title="Laporan Permintaan Barang"></Head>
    <v-container>
        <v-row>
            <v-col>
                <PageTitleHighlightPart first-part-title="Laporan" second-part-title="Permintaan Barang"/>
            </v-col>
        </v-row>
        
        <!-- Filter Section -->
        <v-row class="items-start">
            <v-col cols="12" md="3">
                <DatePicker
                    v-model="startDate"
                    label="Tanggal Mulai"
                    density="compact"
                    :max="endDate"
                />
            </v-col>
            <v-col cols="12" md="3">
                <DatePicker
                    v-model="endDate"
                    label="Tanggal Akhir"
                    density="compact"
                    :min="startDate"
                />
            </v-col>
            <v-col cols="12" md="3">
                <v-select
                    v-model="selectedStatus"
                    :items="statusOptions"
                    item-title="label"
                    item-value="value"
                    label="Status"
                    density="compact"
                    variant="outlined"
                    hide-details
                />
            </v-col>
            <v-col cols="12" md="3" class="flex items-center gap-2">
                <v-btn 
                    variant="tonal" 
                    color="blue-darken-2"
                    @click="applyFilter">
                    <v-icon icon="mdi-filter" class="mr-2" />
                    Filter
                </v-btn>
                <v-btn 
                    variant="tonal" 
                    color="blue-darken-2"
                    @click="downloadSpreadsheet">
                    <v-icon icon="mdi-download" class="mr-2" />
                    Unduh
                </v-btn>
            </v-col>
        </v-row>

        <!-- Table Section -->
        <v-row class="mt-4">
            <v-col>
                <v-data-table 
                    :headers="[
                        { title: 'No', key: 'no', width: '5%' },
                        { title: 'Tanggal Permintaan', key: 'requestDate', width: '10%' },
                        { title: 'Pembuat Permintaan', key: 'requesterName', width: '12%' },
                        { title: 'Nama Barang', key: 'itemName', width: '12%' },
                        { title: 'Nama Spesifikasi', key: 'specificationName', width: '12%' },
                        { title: 'Jumlah Diminta', key: 'requestedQuantity', width: '8%' },
                        { title: 'Jumlah Diterima', key: 'receivedQuantity', width: '8%' },
                        { title: 'Satuan', key: 'unit', width: '7%' },
                        { title: 'Harga Satuan', key: 'price', width: '10%' },
                        { title: 'Status', key: 'status', width: '8%' },
                        { title: 'Petugas', key: 'responder', width: '10%' },
                        { title: 'Tanggal Respon', key: 'responseDate', width: '10%' }
                    ]" 
                    :items="tableData"
                    :items-per-page="itemRequests.per_page"
                    hide-default-footer>
                    <template #headers="{ headers }">
                        <tr class="bg-blue-darken-2">
                            <th v-for="i in (headers.at(0).length)" :key="i">{{ headers.at(0).at(i - 1).title }}</th>
                        </tr>
                    </template>
                    <template #item.requestDate="{ item }">
                        {{ formatDate(item.requestDate) }}
                    </template>
                    <template #item.requestedQuantity="{ item }">
                        {{ item.requestedQuantity.toLocaleString('id-ID') }}
                    </template>
                    <template #item.receivedQuantity="{ item }">
                        {{ item.receivedQuantity.toLocaleString('id-ID') }}
                    </template>
                    <template #item.price="{ item }">
                        {{ formatCurrency(item.price) }}
                    </template>
                    <template #item.status="{ item }">
                        <v-chip 
                            :color="getStatusColor(item.status)" 
                            size="small"
                            variant="flat">
                            {{ getStatusText(item.status) }}
                        </v-chip>
                    </template>
                    <template #item.responseDate="{ item }">
                        {{ formatDate(item.responseDate) }}
                    </template>
                    <template #bottom v-if="false">
                        <tr class="bg-blue-lighten-5 font-weight-bold">
                            <td colspan="11" class="text-right pa-4">Total (berdasarkan permintaan diterima):</td>
                            <td class="pa-4">{{ formatCurrency(total) }}</td>
                        </tr>
                    </template>
                </v-data-table>
                
                <!-- Pagination -->
                <v-row class="mt-4">
                    <v-col class="flex justify-center">
                        <v-pagination
                            v-if="itemRequests.last_page > 1"
                            :length="itemRequests.last_page"
                            :model-value="itemRequests.current_page"
                            @update:model-value="handlePageChange"
                            color="blue-darken-2"
                            :total-visible="7"
                        ></v-pagination>
                    </v-col>
                </v-row>

                <!-- Empty State -->
                <v-row v-if="!tableData || tableData.length === 0">
                    <v-col>
                        <v-card>
                            <v-card-text class="text-center text-grey pa-8">
                                Tidak ada data permintaan barang pada periode ini
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>