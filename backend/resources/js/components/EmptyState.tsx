import type { ReactNode } from 'react';

interface Props {
  icon: ReactNode;
  title: string;
  description?: string;
  action?: ReactNode;
}

export default function EmptyState({ icon, title, description, action }: Props) {
  return (
    <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-12 text-center">
      <div className="w-14 h-14 mx-auto mb-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white/30">
        {icon}
      </div>
      <h3 className="text-base font-semibold text-white mb-1">{title}</h3>
      {description && <p className="text-sm text-white/40 mb-5 max-w-xs mx-auto">{description}</p>}
      {action && <div>{action}</div>}
    </div>
  );
}
