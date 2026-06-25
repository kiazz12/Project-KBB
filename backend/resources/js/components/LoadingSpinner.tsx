export default function LoadingSpinner({ label = 'Memuat...' }: { label?: string }) {
  return (
    <div className="flex items-center justify-center py-12" role="status" aria-label={label}>
      <div className="flex flex-col items-center gap-3">
        <div className="h-8 w-8 border-2 border-gray-200 dark:border-gray-600 border-t-[#003778] dark:border-t-blue-400 rounded-full animate-spin" aria-hidden="true" />
        <p className="text-gray-500 dark:text-gray-400 text-sm">{label}</p>
      </div>
    </div>
  );
}