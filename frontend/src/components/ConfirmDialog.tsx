interface Props {
  open: boolean;
  title: string;
  message: string;
  confirmLabel: string;
  cancelLabel: string;
  variant?: 'danger' | 'primary';
  onConfirm: () => void;
  onCancel: () => void;
}

export default function ConfirmDialog({ open, title, message, confirmLabel, cancelLabel, variant = 'danger', onConfirm, onCancel }: Props) {
  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in" onClick={onCancel}>
      <div className="bg-kbb-900/95 backdrop-blur-xl border border-white/10 rounded-2xl p-6 max-w-sm w-full shadow-2xl shadow-black/40 animate-fade-in-up" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-center gap-3 mb-3">
          <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${
            variant === 'danger' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-gold-400/10 text-gold-400 border border-gold-400/20'
          }`}>
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 className="text-base font-semibold text-white">{title}</h3>
        </div>
        <p className="text-sm text-white/50 mb-6 ml-[3.25rem]">{message}</p>
        <div className="flex justify-end gap-3">
          <button onClick={onCancel} className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 transition">{cancelLabel}</button>
          <button onClick={onConfirm} className={`px-4 py-2 rounded-xl text-sm font-semibold text-white transition ${
            variant === 'danger'
              ? 'bg-red-600 hover:bg-red-500'
              : 'bg-kbb-700 hover:bg-kbb-600'
          }`}>{confirmLabel}</button>
        </div>
      </div>
    </div>
  );
}
