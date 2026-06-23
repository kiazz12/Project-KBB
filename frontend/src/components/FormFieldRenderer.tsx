import type { FormField } from '../types';

interface FormFieldRendererProps {
  field: FormField;
  value: string | string[];
  onChange: (value: string | string[]) => void;
  error?: string;
}

export default function FormFieldRenderer({ field, value, onChange, error }: FormFieldRendererProps) {
  const baseClass = error ? 'border-red-400' : '';

  const renderLabel = () => (
    <label className="text-white/80 text-sm font-medium mb-1.5 block">
      {field.label}
      {field.required && <span className="text-red-400 ml-1">*</span>}
    </label>
  );

  const renderHelp = () => field.help_text && (
    <p className="text-white/40 text-xs mt-1">{field.help_text}</p>
  );

  const renderError = () => error && (
    <p className="text-red-400 text-xs mt-1">{error}</p>
  );

  const val = (value as string) || '';

  switch (field.type) {
    case 'heading':
      return (
        <div className="mb-4">
          <h3 className="text-xl font-bold text-white">{field.label}</h3>
          {field.help_text && <p className="text-white/60 text-sm">{field.help_text}</p>}
        </div>
      );

    case 'paragraph':
      return (
        <div className="mb-4">
          <p className="text-white/70 text-sm">{field.label}</p>
        </div>
      );

    case 'textarea':
      return (
        <div className="mb-4">
          {renderLabel()}
          <textarea
            className={baseClass}
            placeholder={field.placeholder}
            rows={4}
            value={val}
            onChange={(e) => onChange(e.target.value)}
          />
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'select':
      return (
        <div className="mb-4">
          {renderLabel()}
          <select className={baseClass} value={val} onChange={(e) => onChange(e.target.value)}>
            <option value="">{field.placeholder || 'Pilih...'}</option>
            {field.options?.map((opt) => (
              <option key={opt} value={opt}>{opt}</option>
            ))}
          </select>
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'radio':
      return (
        <div className="mb-4">
          {renderLabel()}
          <div className="space-y-2 mt-1">
            {field.options?.map((opt) => (
              <label key={opt} className="flex items-center gap-2 cursor-pointer">
                <input
                  type="radio"
                  name={`field-${field.id}`}
                  value={opt}
                  checked={val === opt}
                  onChange={(e) => onChange(e.target.value)}
                  className="w-4 h-4 accent-gold-400"
                />
                <span className="text-white/80 text-sm">{opt}</span>
              </label>
            ))}
          </div>
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'checkbox':
      const checkedVals = (Array.isArray(value) ? value : val ? [val] : []);
      return (
        <div className="mb-4">
          {renderLabel()}
          <div className="space-y-2 mt-1">
            {field.options?.map((opt) => (
              <label key={opt} className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  value={opt}
                  checked={checkedVals.includes(opt)}
                  onChange={(e) => {
                    const newVal = e.target.checked
                      ? [...checkedVals, opt]
                      : checkedVals.filter((v) => v !== opt);
                    onChange(newVal);
                  }}
                  className="w-4 h-4 accent-gold-400 rounded"
                />
                <span className="text-white/80 text-sm">{opt}</span>
              </label>
            ))}
          </div>
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'email':
      return (
        <div className="mb-4">
          {renderLabel()}
          <input type="email" className={baseClass} placeholder={field.placeholder} value={val} onChange={(e) => onChange(e.target.value)} />
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'number':
      return (
        <div className="mb-4">
          {renderLabel()}
          <input type="number" className={baseClass} placeholder={field.placeholder} value={val} onChange={(e) => onChange(e.target.value)} />
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'date':
      return (
        <div className="mb-4">
          {renderLabel()}
          <input type="date" className={baseClass} value={val} onChange={(e) => onChange(e.target.value)} />
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'time':
      return (
        <div className="mb-4">
          {renderLabel()}
          <input type="time" className={baseClass} value={val} onChange={(e) => onChange(e.target.value)} />
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'signature':
      return (
        <div className="mb-4">
          {renderLabel()}
          <div className="border border-dashed border-white/20 rounded-lg p-8 text-center">
            <p className="text-white/50 text-sm mb-2">Signature Pad (coming soon)</p>
            {val && <p className="text-white/70 text-xs">Signed</p>}
          </div>
          {renderHelp()}
          {renderError()}
        </div>
      );

    case 'file':
      return (
        <div className="mb-4">
          {renderLabel()}
          <input type="file" className="file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:bg-white/10 file:text-white hover:file:bg-white/20" />
          {renderHelp()}
          {renderError()}
        </div>
      );

    default:
      return (
        <div className="mb-4">
          {renderLabel()}
          <input type="text" className={baseClass} placeholder={field.placeholder} value={val} onChange={(e) => onChange(e.target.value)} />
          {renderHelp()}
          {renderError()}
        </div>
      );
  }
}
