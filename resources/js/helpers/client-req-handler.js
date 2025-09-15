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

export function handleSubmit(data) {
  const { form, url, onSuccess, onError } = data;

  form.clearErrors();
  form.post(url, {
    preserveScroll: true,
    onSuccess: (response) => {
      if (response.message) {
        Notify.create({
          message: response.message,
          icon: "info",
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
  const { title, message, url, fetchItemsCallback, loading, data } = options;
  Dialog.create({
    title: title ? title : "Konfirmasi",
    icon: "question",
    message: message,
    focus: "cancel",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    loading.value = true;
    axios
      .post(url, data)
      .then((response) => {
        Notify.create(response.data.message);
        fetchItemsCallback();
      })
      .finally(() => {
        loading.value = false;
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
  const { pagination, props, rows, url, loading, filter, tableRef } = options;

  let source = props ? props.pagination : pagination.value;

  // const filterString = filter ? JSON.stringify(filter) : null;

  let params = {
    page: source.page,
    per_page: source.rowsPerPage,
    order_by: source.sortBy,
    order_type: source.descending ? "desc" : "asc",
    filter: filter,
  };
  // Gunakan pathname sebagai bagian dari kunci localStorage
  // const localStorageKey = `tableParams_${window.location.pathname}`;

  // // Simpan semua parameter ke localStorage, kecuali page yang akan disimpan di URL
  // localStorage.setItem(
  //   localStorageKey,
  //   JSON.stringify({
  //     per_page: params.per_page,
  //     order_by: params.order_by,
  //     order_type: params.order_type,
  //     filter: params.filter,
  //   })
  // );

  // // Perbarui URL dengan page dan filter yang di-stringified
  // const searchParams = new URLSearchParams({
  //   page: source.page,
  //   filter: filter ? JSON.stringify(filter) : "",
  // });
  // window.history.pushState(
  //   {},
  //   "",
  //   `${window.location.pathname}?${searchParams}`
  // );

  loading.value = true;

  axios
    .get(url, { params: params })
    .then((response) => {
      rows.value = response.data.data;
      pagination.value.page = response.data.current_page;
      pagination.value.rowsPerPage = response.data.per_page;
      pagination.value.rowsNumber = response.data.total;
      if (props) {
        pagination.value.sortBy = props.pagination.sortBy;
        pagination.value.descending = props.pagination.descending;
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
