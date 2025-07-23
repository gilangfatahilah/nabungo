<script setup lang="ts">
import { ref, computed } from "vue";
import { Calendar, ChevronLeft, ChevronRight, Clock, Plus, X } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

interface Activity {
  id: string;
  title: string;
  time?: string;
  color: string;
}

interface DayActivities {
  [key: string]: Activity[];
}

const COLORS = [
  "bg-blue-500",
  "bg-green-500",
  "bg-purple-500",
  "bg-red-500",
  "bg-yellow-500",
  "bg-pink-500",
  "bg-indigo-500",
  "bg-orange-500",
];

const currentDate = ref(new Date());
const activities = ref<DayActivities>({});
const selectedDate = ref<string | null>(null);
const showActivityModal = ref(false);
const newActivity = ref({ title: "", time: "" });

const monthNames = [
  "Januari",
  "Februari",
  "Maret",
  "April",
  "Mei",
  "Juni",
  "Juli",
  "Agustus",
  "September",
  "Oktober",
  "November",
  "Desember",
];

const dayNames = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];

const year = computed(() => currentDate.value.getFullYear());
const month = computed(() => currentDate.value.getMonth());
const daysInMonth = computed(() => new Date(year.value, month.value + 1, 0).getDate());
const firstDayOfMonth = computed(() => new Date(year.value, month.value, 1).getDay());
const today = new Date().toDateString();

const navigateMonth = (direction: number) => {
  currentDate.value = new Date(year.value, month.value + direction, 1);
};

const formatDateKey = (day: number) => {
  return `${year.value}-${String(month.value + 1).padStart(2, "0")}-${String(
    day
  ).padStart(2, "0")}`;
};

const isToday = (day: number) => {
  return new Date(year.value, month.value, day).toDateString() === today;
};

const openActivityModal = (day: number) => {
  selectedDate.value = formatDateKey(day);
  showActivityModal.value = true;
};

const addActivity = () => {
  if (!newActivity.value.title.trim() || !selectedDate.value) return;

  const activity: Activity = {
    id: Date.now().toString(),
    title: newActivity.value.title.trim(),
    time: newActivity.value.time,
    color: COLORS[Math.floor(Math.random() * COLORS.length)],
  };

  activities.value[selectedDate.value] = [
    ...(activities.value[selectedDate.value] || []),
    activity,
  ];

  newActivity.value = { title: "", time: "" };
  showActivityModal.value = false;
};

const removeActivity = (dateKey: string, activityId: string) => {
  activities.value[dateKey] =
    activities.value[dateKey]?.filter((a) => a.id !== activityId) || [];
};
</script>

<template>
  <div class="max-w-6xl mx-auto p-4 bg-white">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-2">
        <Calendar class="w-6 h-6 text-blue-600" />
        <h1 class="text-2xl font-bold text-gray-900">Kalendar</h1>
      </div>
      <div class="flex items-center space-x-4">
        <Button variant="ghost" @click="navigateMonth(-1)">
          <ChevronLeft class="w-5 h-5" />
        </Button>
        <h2 class="text-xl font-semibold min-w-48 text-center">
          {{ monthNames[month] }} {{ year }}
        </h2>
        <Button variant="ghost" @click="navigateMonth(1)">
          <ChevronRight class="w-5 h-5" />
        </Button>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="grid grid-cols-7 border-b border-gray-200">
        <div
          v-for="day in dayNames"
          :key="day"
          class="p-4 text-center font-semibold text-gray-700 bg-gray-50"
        >
          {{ day }}
        </div>
      </div>
      <div class="grid grid-cols-7">
        <template :key="'empty-' + i" v-for="i in firstDayOfMonth">
          <div class="h-32"></div>
        </template>

        <template :key="day" v-for="day in daysInMonth">
          <div
            class="h-32 border border-gray-200 p-1 cursor-pointer hover:bg-gray-50"
            :class="{ 'ring-2 ring-blue-500 bg-blue-50': isToday(day) }"
            @click="openActivityModal(day)"
          >
            <div class="flex justify-between items-start mb-1">
              <span
                :class="
                  cn(
                    'text-sm font-medium',
                    isToday(day) ? 'text-blue-600' : 'text-gray-900'
                  )
                "
              >
                {{ day }}
              </span>
              <Plus class="w-4 h-4 text-gray-400 hover:text-gray-600" />
            </div>

            <div class="space-y-1 overflow-hidden">
              <div
                v-for="activity in (activities[formatDateKey(day)] || []).slice(0, 3)"
                :key="activity.id"
                :class="
                  cn(
                    activity.color,
                    'text-white text-xs p-1 rounded truncate flex justify-between items-center group'
                  )
                "
                @click.stop
              >
                <span class="truncate flex-1">
                  <span v-if="activity.time" class="mr-1">{{ activity.time }}</span>
                  {{ activity.title }}
                </span>
                <X
                  class="w-3 h-3 opacity-0 group-hover:opacity-100 cursor-pointer ml-1"
                  @click.stop="removeActivity(formatDateKey(day), activity.id)"
                />
              </div>
              <div
                v-if="(activities[formatDateKey(day)] || []).length > 3"
                class="text-xs text-gray-500 text-center"
              >
                +{{ (activities[formatDateKey(day)] || []).length - 3 }} lainnya
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showActivityModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Tambah Kegiatan</h3>
          <Button variant="ghost" size="icon" @click="showActivityModal = false">
            <X class="w-5 h-5" />
          </Button>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Judul Kegiatan</label
            >
            <Input v-model="newActivity.title" placeholder="Masukkan judul kegiatan..." />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Waktu (Opsional)</label
            >
            <Input type="time" v-model="newActivity.time" />
          </div>
        </div>
        <div class="flex justify-end mt-6 space-x-2">
          <Button variant="outline" @click="showActivityModal = false">Batal</Button>
          <Button :disabled="!newActivity.title.trim()" @click="addActivity"
            >Tambah</Button
          >
        </div>
        <div
          v-if="
            selectedDate && activities[selectedDate] && activities[selectedDate].length
          "
          class="mt-4 pt-4 border-t"
        >
          <h4 class="font-medium text-gray-900 mb-2">Kegiatan Hari Ini:</h4>
          <div class="space-y-2 max-h-40 overflow-y-auto">
            <div
              v-for="activity in activities[selectedDate]"
              :key="activity.id"
              :class="
                cn(
                  activity.color,
                  'text-white p-2 rounded flex justify-between items-center'
                )
              "
            >
              <spn class="flex items-center">
                <Clock v-if="activity.time" class="w-3 h-3 mr-1" />
                <span v-if="activity.time" class="mr-2">{{ activity.time }}</span>
                {{ activity.title }}
              </spn>
              <Button
                variant="ghost"
                size="icon"
                @click="removeActivity(selectedDate, activity.id)"
              >
                <X class="w-4 h-4 text-white hover:text-red-200" />
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Tambahan jika diperlukan */
</style>
