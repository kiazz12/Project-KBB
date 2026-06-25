export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-gray-50">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <img src="/images/kbb-logo.png" alt="KBB" className="h-16 mx-auto mb-3" />
          <h1 className="text-2xl font-bold text-gray-900 tracking-tight">KBB Form</h1>
          <p className="text-gray-500 text-sm mt-1">Pemerintah Kabupaten Bandung Barat</p>
        </div>
        {children}
      </div>
    </div>
  );
}
