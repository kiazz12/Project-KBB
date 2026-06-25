import { Fragment, useState, useEffect } from 'react';
import { Dialog, Transition } from '@headlessui/react';
import type { FormField } from '../types';

const FIELD_TYPES = [
  { value: 'text', label: 'Short Text' },
  { value: 'textarea', label: 'Paragraph' },
  { value: 'email', label: 'Email' },
  { value: 'number', label: 'Number' },
  { value: 'select', label: 'Dropdown' },
  { value: 'radio', label: 'Multiple Choice' },
  { value: 'checkbox', label: 'Checkboxes' },
  { value: 'date', label: 'Date' },
  { value: 'time', label: 'Time' },
  { value: 'file', label: 'File Upload' },
  { value: 'signature', label: 'Signature' },
  { value: 'heading', label: 'Heading' },
  { value: 'paragraph', label: 'Paragraph Text' },
];

interface FieldEditorModalProps {
  open: boolean;
  field: Partial<FormField> | null;
  onSave: (field: Partial<FormField>) => void;
  onClose: () => void;
}

export default function FieldEditorModal({ open, field, onSave, onClose }: FieldEditorModalProps) {
  const [label, setLabel] = useState('');
  const [type, setType] = useState<FormField['type']>('text');
  const [placeholder, setPlaceholder] = useState('');
  const [helpText, setHelpText] = useState('');
  const [required, setRequired] = useState(false);
  const [options, setOptions] = useState('');
  const [minLength, setMinLength] = useState('');
  const [maxLength, setMaxLength] = useState('');
  const [defaultValue, setDefaultValue] = useState('');

  useEffect(() => {
    if (field) {
      setLabel(field.label || '');
      setType(field.type || 'text');
      setPlaceholder(field.placeholder || '');
      setHelpText(field.help_text || '');
      setRequired(field.required || false);
      setOptions(field.options?.join('\n') || '');
      setMinLength(field.min_length?.toString() || '');
      setMaxLength(field.max_length?.toString() || '');
      setDefaultValue(field.default_value || '');
    } else {
      setLabel('');
      setType('text');
      setPlaceholder('');
      setHelpText('');
      setRequired(false);
      setOptions('');
      setMinLength('');
      setMaxLength('');
      setDefaultValue('');
    }
  }, [field, open]);

  const needsOptions = ['select', 'radio', 'checkbox'].includes(type);

  const handleSave = () => {
    onSave({
      ...field,
      label,
      type,
      placeholder,
      help_text: helpText,
      required,
      options: needsOptions ? options.split('\n').filter(Boolean) : undefined,
      min_length: minLength ? parseInt(minLength) : undefined,
      max_length: maxLength ? parseInt(maxLength) : undefined,
      default_value: defaultValue || undefined,
    });
    onClose();
  };

  return (
    <Transition appear show={open} as={Fragment}>
      <Dialog as="div" className="relative z-50" onClose={onClose}>
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black/60" />
        </Transition.Child>
        <div className="fixed inset-0 overflow-y-auto">
          <div className="flex min-h-full items-center justify-center p-4">
            <Transition.Child
              as={Fragment}
              enter="ease-out duration-300"
              enterFrom="opacity-0 scale-95"
              enterTo="opacity-100 scale-100"
              leave="ease-in duration-200"
              leaveFrom="opacity-100 scale-100"
              leaveTo="opacity-0 scale-95"
            >
              <Dialog.Panel className="glass-card w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
                <Dialog.Title className="text-lg font-semibold text-white mb-4">
                  {field?.id ? 'Edit Field' : 'Add Field'}
                </Dialog.Title>

                <div className="space-y-4">
                  <div>
                    <label>Field Type</label>
                    <select value={type} onChange={(e) => setType(e.target.value as FormField['type'])}>
                      {FIELD_TYPES.map((t) => (
                        <option key={t.value} value={t.value}>{t.label}</option>
                      ))}
                    </select>
                  </div>

                  {type !== 'heading' && type !== 'paragraph' && (
                    <>
                      <div>
                        <label>Label <span className="text-red-400">*</span></label>
                        <input value={label} onChange={(e) => setLabel(e.target.value)} placeholder="Field label" />
                      </div>
                      <div>
                        <label>Placeholder</label>
                        <input value={placeholder} onChange={(e) => setPlaceholder(e.target.value)} placeholder="Placeholder text" />
                      </div>
                      <div>
                        <label>Help Text</label>
                        <input value={helpText} onChange={(e) => setHelpText(e.target.value)} placeholder="Help text" />
                      </div>
                      <label className="flex items-center gap-2 text-white/80 text-sm cursor-pointer">
                        <input type="checkbox" checked={required} onChange={(e) => setRequired(e.target.checked)} className="w-4 h-4 accent-gold-400 rounded" />
                        <span>Required</span>
                      </label>
                    </>
                  )}

                  {type === 'heading' && (
                    <div>
                      <label>Heading Text <span className="text-red-400">*</span></label>
                      <input value={label} onChange={(e) => setLabel(e.target.value)} placeholder="Heading text" />
                    </div>
                  )}

                  {type === 'paragraph' && (
                    <div>
                      <label>Paragraph Text</label>
                      <textarea value={label} onChange={(e) => setLabel(e.target.value)} rows={3} placeholder="Paragraph text" />
                    </div>
                  )}

                  {needsOptions && (
                    <div>
                      <label>Options (one per line)</label>
                      <textarea
                        value={options}
                        onChange={(e) => setOptions(e.target.value)}
                        rows={5}
                        placeholder="Option 1&#10;Option 2&#10;Option 3"
                      />
                    </div>
                  )}

                  {type === 'text' && (
                    <div className="grid grid-cols-2 gap-3">
                      <div>
                        <label>Min Length</label>
                        <input type="number" value={minLength} onChange={(e) => setMinLength(e.target.value)} />
                      </div>
                      <div>
                        <label>Max Length</label>
                        <input type="number" value={maxLength} onChange={(e) => setMaxLength(e.target.value)} />
                      </div>
                    </div>
                  )}

                  {(type === 'text' || type === 'number' || type === 'textarea') && (
                    <div>
                      <label>Default Value</label>
                      <input value={defaultValue} onChange={(e) => setDefaultValue(e.target.value)} />
                    </div>
                  )}
                </div>

                <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-white/10">
                  <button onClick={onClose} className="btn-ghost">Cancel</button>
                  <button onClick={handleSave} className="btn-primary" disabled={!label && type !== 'paragraph'}>
                    {field?.id ? 'Update' : 'Add'} Field
                  </button>
                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition>
  );
}
