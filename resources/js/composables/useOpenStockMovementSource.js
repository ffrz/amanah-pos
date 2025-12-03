import axios from "axios";
import { Notify } from "quasar";

const useOpenStockMovementSource = async (row) => {
  let url = "";
  if (row.parent_ref_type == "sales_order") {
    url = route("admin.sales-order.detail", { id: row.parent_id });
  } else if (row.ref_type == "sales_order_return_detail") {
    url = route("admin.sales-order-return.detail", { id: row.parent_id });
  } else if (row.ref_type == "purchase_order_detail") {
    url = route("admin.purchase-order.detail", { id: row.parent_id });
  } else if (row.ref_type == "purchase_order_return_detail") {
    url = route("admin.purchase-order-return.detail", {
      id: row.parent_id,
    });
  } else if (row.ref_type == "stock_adjustment_detail") {
    url = route("admin.stock-adjustment.detail", { id: row.parent_id });
  }
  else {
    Notify.create({
      message: 'Tidak ada rincian untuk riwayat ini.',
    });
    return;
  }

  window.open(url, "_blank");
};

export default useOpenStockMovementSource;
