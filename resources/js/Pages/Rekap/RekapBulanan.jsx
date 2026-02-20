import { Head, usePage, router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useState } from "react";

// export default function RekapBulanan({ rekaps, units, subUnits, filters }) 
export default function RekapBulanan({
    rekaps,
    units = [],
    subUnitsGrouped = {},
    statusPegawais = [],
    filters = {}
}) {

    const [bulan, setBulan] = useState(filters.bulan || "");
    const [unit, setUnit] = useState(filters.unit || "");
    const [selectedSubUnitByRow, setSelectedSubUnitByRow] = useState({});
    const [selectedStatusByRow, setSelectedStatusByRow] = useState({});
    const [showTable, setShowTable] = useState(
        Boolean((filters.bulan || "").trim() || String(filters.unit || "").trim())
    );

    const getSelectedSubUnit = (row) =>
        selectedSubUnitByRow[row.id] ?? (row.sub_unit_id ? String(row.sub_unit_id) : "");

    const getSelectedStatus = (row) =>
        selectedStatusByRow[row.id] ?? (row.status_kepegawaian || "");

    const submitFilter = () => {
        setShowTable(true);
        router.get(route("rekap-bulanan.index"), {
            bulan,
            unit
        }, { preserveState: true });
    };

    // console.log(units);
    // console.log("subUnits:", subUnits);

    const formatBulan = (date) => {
        if (!date) return "-";

        return new Intl.DateTimeFormat("id-ID", {
            year: "numeric",
            month: "short"
        }).format(new Date(date));
    };


    return (
        <AdminLayout>
            <Head title="Rekap Bulanan" />

            <div className="p-4">

                {/* TITLE */}
                <h2 className="text-lg font-semibold border-b pb-2 mb-4">
                    Daftar Rekapitulasi Bulanan
                </h2>

                {/* FILTER */}
                <div className="flex gap-3 items-center mb-4">

                    {/* UNIT */}
                    <select
                        value={unit}
                        onChange={e => setUnit(e.target.value)}
                        className="border rounded px-3 py-2"
                    >
                        <option value="">-- Pilih Unit --</option>
                        {units.map(u => (
                            <option key={u.id} value={u.id}>
                                {u.unit}
                            </option>
                        ))}
                    </select>

                    {/* BULAN */}
                    <input
                        type="month"
                        value={bulan}
                        onChange={e => setBulan(e.target.value)}
                        className="border rounded px-3 py-2"
                    />

                    {/* SUBMIT */}
                    <button
                        onClick={submitFilter}
                        className="btn btn-success"
                    >
                        Submit
                    </button>
                </div>

                {/* TABLE */}
                {showTable && (
                <div className="bg-white shadow rounded overflow-hidden">
                    <table className="w-full text-sm border">

                        <thead className="bg-gray-100 text-gray-700">
                            <tr>
                                <th className="p-2 border">Bulan</th>
                                <th className="p-2 border">Unit</th>
                                <th className="p-2 border">UnitId</th>
                                <th className="p-2 border">Pilih SubUnit</th>
                                <th className="p-2 border">Pilih Status</th>
                                <th className="p-2 border">Options</th>
                                <th className="p-2 border">Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>
                            {rekaps.data.map(row => (
                                <tr key={row.id} className="hover:bg-gray-50">

                                    <td className="p-2 border">
                                        {formatBulan(row.date)}
                                    </td>

                                    <td className="p-2 border">
                                        {row.unit?.unit}

                                    </td>

                                    <td className="p-2 border text-center">
                                        {row.unit_id}
                                    </td>

                                    {/* SUB UNIT */}
                                    <td className="p-2 border">

                                        <select
                                            value={getSelectedSubUnit(row)}
                                            onChange={(e) =>
                                                setSelectedSubUnitByRow((prev) => ({
                                                    ...prev,
                                                    [row.id]: e.target.value
                                                }))
                                            }
                                            className="border rounded px-2 py-2 w-full"
                                        >
                                            <option value="">-</option>

                                            {(subUnitsGrouped[row.unit_id] || []).map(s => (
                                                <option key={s.id} value={s.id}>
                                                    {s.sub_unit}
                                                </option>
                                            ))}
                                        </select>


                                    </td>

                                    {/* STATUS */}
                                    <td className="p-2 border">
                                        <select
                                            value={getSelectedStatus(row)}
                                            onChange={(e) =>
                                                setSelectedStatusByRow((prev) => ({
                                                    ...prev,
                                                    [row.id]: e.target.value
                                                }))
                                            }
                                            className="border rounded px-2 py-2 w-full"
                                        >
                                            <option value="">-</option>
                                            {statusPegawais.map(status => (
                                                <option key={status.id} value={status.code}>
                                                    {status.label || status.code}
                                                </option>
                                            ))}
                                        </select>
                                    </td>

                                    {/* OPTIONS */}
                                    <td className="p-2 border text-center">
                                        <button
                                            onClick={() =>
                                                window.open(
                                                    route("rekap.print", {
                                                        unit_id: row.unit_id,
                                                        sub_unit_id: getSelectedSubUnit(row),
                                                        bulan: filters.bulan,
                                                        status_pegawai: getSelectedStatus(row)
                                                    }),
                                                    "rekap-print-tab"
                                                )
                                            }
                                            className="btn btn-danger btn-xs"
                                        >
                                            Print 
                                        </button>

                                    </td>

                                    <td className="p-2 border"></td>

                                </tr>
                            ))}
                        </tbody>

                    </table>

                    {/* PAGINATION */}
                    <div className="p-3 border-t text-sm flex justify-between items-center">
                        <span>
                            Showing {rekaps.from} to {rekaps.to} of {rekaps.total} entries
                        </span>

                        <div className="flex gap-1">
                            {rekaps.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    onClick={() => router.visit(link.url)}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    className={`px-3 py-1 border rounded
                                        ${link.active ? "bg-blue-600 text-white" : "bg-white"}
                                    `}
                                />
                            ))}
                        </div>
                    </div>
                </div>
                )}
            </div>
        </AdminLayout>
    );
}
