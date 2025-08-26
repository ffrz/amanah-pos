import { createOptions } from "@/helpers/options";

export default function useCustomerWalletTransactionConfirmationStatusOptions(
  includeAllOptions = false
) {
  let statusOptions = createOptions(
    window.CONSTANTS.CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES
  );

  if (includeAllOptions) {
    statusOptions.unshift({ value: "all", label: "Semua Status" });
  }

  return statusOptions;
}
