import React from "https://esm.sh/react@18";
import { createRoot } from "https://esm.sh/react-dom@18/client";
import { Ticket, ShoppingCart, FileText, Settings, LogOut } from "https://esm.sh/lucide-react@latest";

function Sidebar({ user }) {
  const menu = [
    { href: "/admin/rifas", label: "Rifas", icon: Ticket },
    { href: "/admin/ordenes", label: "Órdenes", icon: ShoppingCart },
    { href: "/admin/reportes", label: "Reportes", icon: FileText },
    { href: "/admin/ajustes", label: "Ajustes", icon: Settings },
    { href: "/admin/logout", label: "Salir", icon: LogOut },
  ];
  const pathname = window.location.pathname;
  return (
    <div className="flex flex-col h-full">
      <div className="flex items-center justify-center h-16 border-b border-gray-800">
        <img src="/img/logo.svg" alt="Logo" className="w-10 h-10" />
      </div>
      <div className="flex flex-col items-center mt-4">
        <img
          src={user.avatar || "https://via.placeholder.com/48"}
          alt="Avatar"
          className="w-12 h-12 rounded-full border border-gray-700"
        />
        <span className="mt-2 text-sm font-medium opacity-0 group-hover:opacity-100 transform group-hover:translate-x-0 translate-x-2 transition-all duration-300 whitespace-nowrap">
          {user.name || "Usuario"}
        </span>
      </div>
      <nav className="mt-6 flex-1" aria-label="Sidebar">
        {menu.map((item) => {
          const Icon = item.icon;
          const active = pathname.startsWith(item.href);
          return (
            <a
              key={item.href}
              href={item.href}
              className={`flex items-center px-4 py-3 my-1 rounded-md transition-colors duration-200 focus:outline-none focus-visible:ring-2 ring-offset-2 ring-offset-gray-900 ${
                active
                  ? "bg-gray-800 text-white"
                  : "text-gray-400 hover:text-white hover:bg-gray-800"
              }`}
              aria-current={active ? "page" : undefined}
            >
              <Icon className="w-5 h-5" />
              <span className="ml-3 opacity-0 group-hover:opacity-100 transform group-hover:translate-x-0 translate-x-2 transition-all duration-300 whitespace-nowrap">
                {item.label}
              </span>
            </a>
          );
        })}
      </nav>
    </div>
  );
}

const container = document.getElementById("admin-sidebar");
const user = JSON.parse(container.dataset.user || "{}");
const root = createRoot(container);
root.render(<Sidebar user={user} />);
