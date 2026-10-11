/**
 * Shared Costs & Expenses UI options (legacy payment categories / methods).
 */

export const PAYMENT_STATUS_OPTIONS = [
  { title: 'All', value: '' },
  { title: 'Paid', value: 'Paid' },
]

export const PAYMENT_TYPE_CATEGORY_OPTIONS = [
  { title: 'Select', value: null },
  { title: 'General Supplies/Goods', value: 'General Supplies/Goods' },
  { title: 'Repairs & Maintenance', value: 'Repairs & Maintenance' },
  { title: 'Vendor Contracts/Services', value: 'Vendor Contracts/Services' },
  { title: 'Utilities', value: 'Utilities' },
  { title: 'Taxes, Licenses & Fees', value: 'Taxes, Licenses & Fees' },
  { title: 'Financial Adjustments/Other', value: 'Financial Adjustments/Other' },
]

export const EXPENSE_PAYMENT_METHOD_OPTIONS = [
  { title: 'Select', value: null },
  { title: 'Credit Card', value: 'Credit Card', convenience_fee: 5, management_markup: 0 },
  { title: 'Debit Card', value: 'Debit Card', convenience_fee: 3, management_markup: 0 },
  { title: 'Check', value: 'Check', convenience_fee: 0, management_markup: 0 },
  { title: 'ACH / Wire', value: 'ACH / Wire', convenience_fee: 0, management_markup: 2.5 },
  { title: 'Cash', value: 'Cash', convenience_fee: 0, management_markup: 0 },
  { title: 'Owner Reimbursement', value: 'Owner Reimbursement', convenience_fee: 0, management_markup: 0 },
]

export const CONVENIENCE_FEE_CATEGORIES = ['General Supplies/Goods']

export const ADMINISTRATIVE_FEE_CATEGORIES = [
  'Repairs & Maintenance',
  'Vendor Contracts/Services',
  'Utilities',
  'Taxes, Licenses & Fees',
]

export const RECURRING_MONTHS_OPTIONS = Array.from({ length: 12 }, (_, index) => ({
  title: String(index + 1),
  value: index + 1,
}))

export const categoryShowsConvenienceFee = category => CONVENIENCE_FEE_CATEGORIES.includes(category)

export const categoryShowsAdministrativeFee = category => ADMINISTRATIVE_FEE_CATEGORIES.includes(category)

export const computePaymentTotal = ({
  initialPrice = 0,
  convenienceFee = 0,
  managementMarkup = 0,
  category = null,
}) => {
  const base = Number(initialPrice) || 0

  if (categoryShowsConvenienceFee(category)) {
    const fee = base > 25 ? (Number(convenienceFee) || 0) : 0

    return Number((base + fee).toFixed(2))
  }

  if (categoryShowsAdministrativeFee(category)) {
    const markup = Number(managementMarkup) || 0

    return Number((base + (base * markup) / 100).toFixed(2))
  }

  return Number(base.toFixed(2))
}
