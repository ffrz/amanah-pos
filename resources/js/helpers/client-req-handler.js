import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { Notify, Dialog } from "quasar";
import { nextTick } from "vue";
import dayjs from "dayjs";

const _scrollToFirstError = () => {
  const page = usePage();
  const firstErrorKey = Object.keys(page.props.errors)[0];
  if (firstErrorKey) {
    setTimeout(() => {
      const errorElement = document.querySelector(".q-field--error input");
      if (errorElement) {
        errorElement.scrollIntoView({ behavior: "smooth", block: "center" });
        errorElement.focus();
      }
    }, 0);
  }
};

/**
 * Transforms an Inertia form's payload by converting specified Date/Time fields
 * into strings with a given format.
 * * @param {object} form The Inertia form object.
 * @param {object} fields An object where keys are field names and values are their formats.
 * Example: { 'start_date': 'YYYY-MM-DD', 'end_time': 'HH:mm', 'transaction_dt': 'YYYY-MM-DD HH:mm:ss' }
 */
export const transformPayload = (form, fields) => {
  form.transform((data) => {
    const payload = { ...data };

    for (const field in fields) {
      if (payload[field] instanceof Date) {
        payload[field] = dayjs(payload[field]).format(fields[field]);
      }
    }

    return payload;
  });
};

export async function handleSubmit(data) {
  const { form, url, onSuccess, onError } = data;

  form.clearErrors();
  await form.post(url, {
    preserveScroll: true,
    onSuccess: (response) => {
      if (response.message) {
        Notify.create({
          message: response.message,
          color: "grey",
          actions: [
            { icon: "close", color: "white", round: true, dense: true },
          ],
        });
      }

      if (response.data?.id) {
        form.id = response.data.id;
      }

      if (onSuccess) {
        onSuccess(response);
      }
    },
    onError: (error) => {
      _scrollToFirstError();
      if (
        !error ||
        typeof error.message !== "string" ||
        error.message.length === 0
      )
        return;

      if (error.response?.status !== 422) {
        // jangan tampilkan kalau error validasi
        Notify.create({
          message: error.message,
          icon: "info",
          color: "negative",
          actions: [
            { icon: "close", color: "white", round: true, dense: true },
          ],
        });
      }

      if (onError) {
        onError(response);
      }
    },
  });
}

export function handlePost(options) {
  const { title, message, url, fetchItemsCallback, onSuccess, loading, data } =
    options;
  Dialog.create({
    title: title ? title : "Konfirmasi",
    icon: "question",
    message: message,
    focus: "cancel",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    if (loading) {
      loading.value = true;
    }
    axios
      .post(url, data)
      .then((response) => {
        Notify.create(response.data.message);
        if (fetchItemsCallback) {
          fetchItemsCallback();
        }
        if (onSuccess) {
          onSuccess();
        }
      })
      .finally(() => {
        if (loading) {
          loading.value = false;
        }
      })
      .catch((error) => {
        let message = "";
        if (error.response.data && error.response.data.message) {
          message = error.response.data.message;
        } else if (error.message) {
          message = error.message;
        }

        if (message.length > 0) {
          Notify.create({ message: message, color: "red" });
        }
        console.log(error);
      });
  });
}

// alias
export function handleDelete(data) {
  return handlePost(data);
}

export function handleFetchItems(options) {
  const { pagination, props, rows, url, loading, filter, tableRef, onSuccess } =
    options;

  let source = props ? props.pagination : pagination.value;

  // --- PARAMETER UNTUK SERVER ---
  let params = {
    // Di sini kita tetap mengirim 'page', 'per_page', dll.
    // Laravel secara internal akan menentukan apakah akan menggunakan 'page' (jika LengthAware)
    // atau 'cursor' (jika ada di URL query string).
    page: source.page,
    per_page: source.rowsPerPage,
    order_by: source.sortBy,
    order_type: source.descending ? "desc" : "asc",
    filter: filter,
  };

  // Jika Anda sudah berada di mode kursor, tambahkan kursor ke params
  // Ini penting agar navigasi Prev/Next tetap berfungsi.
  // Asumsikan kursor disimpan di pagination.value
  if (pagination.value.nextCursor) {
    params.cursor = pagination.value.nextCursor;
  }
  // Catatan: Biasanya Quasar/VueJS akan menangani kursor melalui URL secara otomatis.
  // Untuk kesederhanaan, kita fokus pada parsing respons.

  loading.value = true;

  axios
    .get(url, { params: params })
    .then((response) => {
      const apiResponse = response.data.status ? response.data : response;
      const data = apiResponse.data.data;
      const paginationData = apiResponse.data;

      rows.value = data;

      // ====================================================================
      // === LOGIKA BARU: DETEKSI PAGINATION TYPE (LENGTH AWARE vs CURSOR) ===
      // ====================================================================

      if (paginationData.next_cursor !== undefined) {
        // --- RESPON CURSOR PAGINATOR (Solusi Cepat) ---

        // Simpan kursor berikutnya dan sebelumnya untuk navigasi
        pagination.value.nextCursor = paginationData.next_cursor || null;
        pagination.value.prevCursor = paginationData.prev_cursor || null;

        // Penting: Hapus 'rowsNumber' (total) dan 'page' agar UI tidak menampilkan angka halaman
        // yang salah atau mencoba melompat ke page index yang tidak ada.
        pagination.value.rowsNumber = data.length * 2; // Beri nilai dummy > perPage
        pagination.value.page = 1; // Set ke 1 atau biarkan saja (tergantung kebutuhan UI)

        // Catatan: Di sini, tombol navigasi Quasar QTable perlu diubah
        // agar hanya menampilkan tombol Prev/Next berdasarkan nextCursor/prevCursor.
      } else {
        // --- RESPON LENGTH AWARE PAGINATOR (Respons Lama/Klasik) ---

        pagination.value.page = paginationData.current_page;
        pagination.value.rowsPerPage = paginationData.per_page;
        pagination.value.rowsNumber = paginationData.total;

        // Reset kursor
        pagination.value.nextCursor = null;
        pagination.value.prevCursor = null;
      }

      // ====================================================================

      if (props) {
        pagination.value.sortBy = props.pagination.sortBy;
        pagination.value.descending = props.pagination.descending;
      }

      if (onSuccess) {
        onSuccess(response);
      }
    })
    .finally(() => {
      loading.value = false;
      nextTick(() => {
        if (!tableRef || !tableRef.value) return;
        const scrollableElement =
          tableRef.value.$el.querySelector(".q-table__middle");
        if (!scrollableElement) return;
        scrollableElement.scrollTop = 0;
      });
    });
}

export async function handleLoadForm(data) {
  const { form, url, onSuccess, onError } = data;
  try {
    form.reset();
    form.clearErrors();
    form.processing = true;

    const response = await axios.get(url);
    form.setData(response.data.data);
    if (onSuccess) onSuccess(response);
  } catch (error) {
    console.error("Quick Create Failed:", error);
    Notify.create({
      type: "negative",
      message: "Gagal mengambil data awal pelanggan.",
    });
    if (onError) onError(response);
  } finally {
    form.processing = false;
  }
}
