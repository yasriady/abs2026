import { useState, useEffect } from "react";
import { Head, router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import Modal from "@/Components/Modal";
import { usePage } from "@inertiajs/react";
import axios from "axios";

export default function AbsensiHarian({
    pegawais,
    units = [],
    subUnits = [],
    filters = {},
    stats = {},
    // statuses = [],   // ← tambah ini
    statusDay = [],
    statusIn = [],
    // statusOut = [],
}) {

    const fotoUrl = (id) => id ? `/pegawai/foto/${id}` : '/images/no-image.png'

    const absensiFotoIn = (summary) =>
        summary?.time_in_final
            ? `/absensi/foto/in/${summary.id}?t=${summary.updated_at || Date.now()}`
            : '/images/no-tap.png'

    const absensiFotoOut = (summary) =>
        summary?.time_out_final
            ? `/absensi/foto/out/${summary.id}?t=${summary.updated_at || Date.now()}`
            : '/images/no-tap.png'

    const [unitId, setUnitId] = useState(filters.unit_id || "");
    const [subUnitId, setSubUnitId] = useState(filters.sub_unit_id || "");
    const [date, setDate] = useState(
        filters.date || new Date().toISOString().split("T")[0]
    );
    const [search, setSearch] = useState(filters.search || "");
    // const { unit_id, sub_unit_id, date, search } = filters

    const handleFilter = () => {
        router.get(
            "/absensi-harian",
            {
                unit_id: unitId,
                sub_unit_id: subUnitId,
                date: date,
                search: search,
            },
            { preserveState: true }
        );
    };

    const [showModal, setShowModal] = useState(false);
    const [selectedSummary, setSelectedSummary] = useState(null);
    const [form, setForm] = useState({
        status: "",
        notes: ""
    });

    const filteredStatus = (type) => {
        if (type === "day") return statuses.filter(s => s.day)
        if (type === "in") return statuses.filter(s => s.in)
        if (type === "out") return statuses.filter(s => s.out)
        return statuses
    }

    // console.log(statusDay)
    // console.log(statusIn)
    // // console.log(statusOut)


    const [loadingUnit, setLoadingUnit] = useState(false)

    const regenerateUnit = async () => {

        if (!unitId) {
            alert("Pilih unit dulu")
            return
        }

        setLoadingUnit(true)

        try {
            await axios.post("/absensi/regenerate-unit", {
                date: date,
                unit_id: unitId
            })

            router.reload()
        }
        catch (err) {
            console.error(err)
            alert("Gagal regenerate unit")
        }
        finally {
            setLoadingUnit(false)
        }
    }


    /* ================= MODAL JAM ================= */

    const [jamModal, setJamModal] = useState(false)
    const [jamType, setJamType] = useState(null)
    const [jamData, setJamData] = useState(null)

    const [jamForm, setJamForm] = useState({
        status: "",
        jam: "",
        menit: "",
        notes: ""
    })

    const openJamModal = (summary, type) => {

        if (!summary) return

        const time =
            type === "in"
                ? summary.time_in_final
                : summary.time_out_final

        const [jam = "00", menit = "00"] = (time || "").split(":")

        setJamType(type)
        setJamData(summary)
        setJamModal(true)

        setJamForm({
            status:
                type === "in"
                    ? summary.status_masuk_final
                    : summary.status_pulang_final,

            jam,
            menit,

            notes:
                type === "in"
                    ? summary.notes_in || ""
                    : summary.notes_out || ""
        })
    }

    const [loadingMap, setLoadingMap] = useState({})

    const regeneratePegawai = async (nik) => {

        setLoadingMap(prev => ({ ...prev, [nik]: true }))

        try {
            await axios.post(route("absensi.regenerate.nik"), {
                nik: nik,
                date: date
            })
        }
        catch (err) {
            console.error(err)
            alert("Gagal menjalankan ETL")
        }
        finally {
            setLoadingMap(prev => ({ ...prev, [nik]: false }))
        }
    }

    console.log(route)

    return (
        <AdminLayout title="Absensi Harian">
            <Head title="Absensi Harian" />

            <div className="box box-primary">
                {/* ================= FILTER ================= */}
                <div className="box-header with-border">
                    <div className="row">
                        <div className="col-md-3">
                            <label>Unit</label>
                            <select
                                className="form-control"
                                value={unitId}
                                onChange={(e) => {
                                    const value = e.target.value;
                                    setUnitId(value);
                                    setSubUnitId(""); // reset sub unit

                                    router.get(
                                        "/absensi-harian",
                                        {
                                            unit_id: value,
                                            date: date,
                                            search: search,
                                        },
                                        {
                                            replace: true
                                        }
                                    );
                                }}
                            >
                                <option value="">-</option>
                                {units.map((u) => (
                                    <option key={u.id} value={u.id}>
                                        {u.unit}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Sub</label>
                            <select
                                className="form-control"
                                value={subUnitId}
                                onChange={(e) =>
                                    setSubUnitId(e.target.value)
                                }
                            >
                                <option value="">-</option>
                                {subUnits.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.sub_unit}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Tgl</label>
                            <input
                                type="date"
                                className="form-control"
                                value={date}
                                onChange={(e) =>
                                    setDate(e.target.value)
                                }
                            />
                        </div>

                        <div className="col-md-3">
                            <label>&nbsp;</label>
                            <button
                                className="btn btn-primary btn-block"
                                onClick={handleFilter}
                            >
                                Show
                            </button>
                            <button
                                onClick={regenerateUnit}
                                disabled={loadingUnit}
                                className="px-3 py-2 bg-blue-600 text-white rounded"
                                style={{ opacity: loadingUnit ? 0.6 : 1 }}
                            >
                                {loadingUnit ? "Processing..." : "Regenerate Absensi"}
                            </button>

                        </div>




                    </div>
                </div>

                {/* ================= TABLE ================= */}
                <div className="box-body">

                    {/* Top Bar */}
                    <div className="row" style={{ marginBottom: 10 }}>
                        <div className="col-sm-6">
                            <strong>
                                Menampilkan {stats.from || 0} - {stats.to || 0}
                                {" "} dari {stats.total || 0} data
                            </strong>
                        </div>

                        <div className="col-sm-6 text-right">
                            Search:
                            <input
                                type="text"
                                className="form-control input-sm"
                                style={{
                                    width: 200,
                                    display: "inline-block",
                                    marginLeft: 5,
                                }}
                                value={search}
                                onChange={(e) =>
                                    setSearch(e.target.value)
                                }
                                onKeyDown={(e) => {
                                    if (e.key === "Enter") {
                                        handleFilter();
                                    }
                                }}
                            />
                        </div>
                    </div>

                    <div className="table-responsive">
                        <table className="table table-bordered table-hover" style={{ fontSize: "95%" }}>
                            <thead>
                                <tr>
                                    <th width="25%">IDENTITY</th>
                                    <th width="15%">REF</th>
                                    <th width="15%">MASUK</th>
                                    <th width="15%">PULANG</th>
                                    <th width="30%">KET</th>
                                </tr>
                            </thead>
                            <tbody>
                                {pegawais.data.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="text-center">
                                            Tidak ada data
                                        </td>
                                    </tr>
                                )}

                                {pegawais.data.map((pegawai) => {
                                    const summary = pegawai.summary;

                                    return (
                                        <tr key={pegawai.id}>
                                            {/* IDENTITY */}
                                            <td>
                                                <strong>{pegawai.nama}</strong>
                                                <br />
                                                <small>NIK: {pegawai.nik}</small>
                                                <br />
                                                <small>NIP: {pegawai.nip}</small>
                                            </td>

                                            {/* REF */}
                                            <td className="text-left">


                                                <div style={{ position: 'relative', width: 55, height: 65 }}>
                                                    <a href={fotoUrl(pegawai.id)} data-lightbox="pegawai">
                                                        <img
                                                            src={fotoUrl(pegawai.id)}
                                                            onError={(e) => e.target.src = '/images/no-image.png'}
                                                            style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                                        />
                                                    </a>
                                                    <button
                                                        onClick={() => regeneratePegawai(pegawai.nik)}
                                                        disabled={loadingMap[pegawai.nik]}
                                                        style={{
                                                            display: 'none',
                                                            position: 'absolute',
                                                            top: -6,
                                                            right: -6,
                                                            width: 20,
                                                            height: 20,
                                                            borderRadius: '50%',
                                                            border: 'none',
                                                            background: '#0ea5e9',
                                                            color: 'white',
                                                            fontSize: 11,
                                                            cursor: 'pointer',
                                                            opacity: loadingMap[pegawai.nik] ? 0.5 : 1
                                                        }}
                                                        title="Regenerate summary"
                                                    >
                                                        {loadingMap[pegawai.nik]
                                                            ? <i className="fa fa-spinner fa-spin"></i>
                                                            : "↻"
                                                        }
                                                    </button>

                                                </div>

                                                <div style={{ fontSize: 12, marginTop: 6 }}>
                                                    {filters.date}
                                                </div>


                                                {summary ? (

                                                    <span
                                                        style={{ cursor: "pointer" }}
                                                        onClick={() => {
                                                            setSelectedSummary(summary);
                                                            setForm({
                                                                status: summary.status_hari_final || "",
                                                                notes: summary.notes_hari || ""
                                                            });
                                                            setShowModal(true);
                                                        }}
                                                        className={`label ${summary.status_hari_final === "HADIR"
                                                            ? "label-success"
                                                            : "label-danger"
                                                            }`}
                                                    >
                                                        {summary.status_hari_final}
                                                    </span>
                                                ) : (
                                                    <span className="label label-default">
                                                        -
                                                    </span>
                                                )}

                                            </td>

                                            {/* MASUK */}
                                            <td className="text-left">

                                                <a href={absensiFotoIn(summary)} data-lightbox="pegawai">
                                                    <img
                                                        src={absensiFotoIn(summary)}
                                                        onError={(e) => e.target.src = '/images/no-tap.png'}
                                                        style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                                    />
                                                </a>

                                                <div style={{ fontSize: 14, fontWeight: 600 }}>
                                                    {summary?.time_in_final
                                                        ? summary.time_in_final.slice(0, -3)  // Menghapus detik (SS)
                                                        : "-"}
                                                    {summary?.attribute_in && (
                                                        <span style={{ fontSize: 11, color: "#777", marginLeft: 4 }}>
                                                            {summary.attribute_in}
                                                        </span>
                                                    )}
                                                </div>

                                                <div>
                                                    <span
                                                        onClick={() => summary && openJamModal(summary, "in")}
                                                        style={{ cursor: "pointer" }}
                                                        className={`label ${summary?.status_masuk_final ===

                                                            "HADIR"
                                                            ? "label-success"
                                                            : "label-danger"
                                                            }`}
                                                    >
                                                        {summary?.status_masuk_final || "-"}
                                                    </span>
                                                    &nbsp;
                                                    <span>
                                                        <i className="fa fa-map-marker"></i>
                                                        &nbsp;
                                                        {summary?.machine_in && (
                                                            <span style={{ fontSize: 11, color: "#999" }}>
                                                                {summary.machine_in}
                                                            </span>
                                                        )}
                                                    </span>
                                                </div>

                                            </td>

                                            {/* PULANG */}
                                            <td className="text-left">

                                                <a href={absensiFotoOut(summary)} data-lightbox="pegawai">
                                                    <img
                                                        src={absensiFotoOut(summary)}
                                                        onError={(e) => e.target.src = '/images/no-tap.png'}
                                                        style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                                    />
                                                </a>

                                                <div style={{ fontSize: 14, fontWeight: 600 }}>
                                                    {summary?.time_out_final
                                                        ? summary.time_out_final.slice(0, -3)  // Menghapus detik (SS)
                                                        : "-"}
                                                    {summary?.attribute_out && (
                                                        <span style={{ fontSize: 11, color: "#777", marginLeft: 4 }}>
                                                            {summary.attribute_out}
                                                        </span>
                                                    )}
                                                </div>

                                                <div>
                                                    <span
                                                        onClick={() => summary && openJamModal(summary, "out")}
                                                        style={{ cursor: "pointer" }}
                                                        className={`label ${summary?.status_pulang_final ===

                                                            "HADIR"
                                                            ? "label-success"
                                                            : "label-danger"
                                                            }`}
                                                    >
                                                        {summary?.status_pulang_final || "-"}
                                                    </span>
                                                    &nbsp;
                                                    <span>
                                                        <i className="fa fa-map-marker"></i>
                                                        &nbsp;
                                                        {summary?.machine_out && (
                                                            <span style={{ fontSize: 11, color: "#999" }}>
                                                                {summary.machine_out}
                                                            </span>
                                                        )}
                                                    </span>
                                                </div>


                                            </td>

                                            {/* KET */}
                                            <td>
                                                {/* <div>
                                                    {summary?.final_note || "-"}
                                                </div> */}

                                                {summary?.notes_hari && (
                                                    <div>Hari: {summary.notes_hari}</div>
                                                )}

                                                {summary?.notes_in && (
                                                    <div>Pagi: {summary.notes_in}</div>
                                                )}
                                                {summary?.notes_out && (
                                                    <div>Sore: {summary.notes_out}</div>
                                                )}

                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="text-center">
                        {pegawais.links.map((link, index) => (
                            <button
                                key={index}
                                className={`btn btn-sm ${link.active
                                    ? "btn-primary"
                                    : "btn-default"
                                    }`}
                                disabled={!link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                onClick={() =>
                                    link.url && router.visit(link.url)
                                }
                                style={{ margin: 2 }}
                            />
                        ))}
                    </div>

                </div>
            </div>



            <Modal
                show={showModal}
                maxWidth="md"
                onClose={() => setShowModal(false)}
            >
                <div className="p-6">

                    <h3 className="text-lg font-semibold mb-4">
                        Edit Status Harian
                    </h3>

                    <select
                        className="w-full border rounded p-2 mb-3"
                        value={form.status}
                        onChange={e => setForm({ ...form, status: e.target.value })}
                    >
                        {statusDay.map(s => (
                            <option key={s.name} value={s.name}>
                                {s.name} — {s.desc}
                            </option>
                        ))}
                    </select>


                    <textarea
                        className="w-full border rounded p-2 mb-4"
                        placeholder="Komentar / alasan"
                        value={form.notes}
                        onChange={e => setForm({ ...form, notes: e.target.value })}
                    />

                    <div className="flex justify-between">

                        <button
                            className="px-4 py-2 bg-gray-200 rounded"
                            onClick={() => setShowModal(false)}
                        >
                            Tutup
                        </button>

                        <div className="flex gap-2">

                            <button
                                className="px-4 py-2 bg-cyan-500 text-white rounded"
                                onClick={() => {
                                    setForm({
                                        status: selectedSummary.status_hari_final,
                                        notes: selectedSummary.notes_hari || ""
                                    })
                                }}
                            >
                                Reset
                            </button>

                            <button
                                className="px-4 py-2 bg-blue-600 text-white rounded"
                                onClick={() => {
                                    router.post("/absensi/update-status", {
                                        id: selectedSummary.id,
                                        status: form.status,
                                        notes: form.notes
                                    }, {
                                        preserveScroll: true,
                                        onSuccess: () => setShowModal(false)
                                    })
                                }}
                            >
                                Simpan
                            </button>

                        </div>
                    </div>

                </div>
            </Modal>


            <Modal
                show={jamModal}
                onClose={() => setJamModal(false)}
                title={`Kehadiran ${filters.date} / ${jamType}`}
                size="4xl"
                variant="success"
                scrollable={false}
                footer={
                    <div className="flex justify-between">

                        <button
                            onClick={() => setJamModal(false)}
                            className="px-4 py-2 bg-gray-200 rounded"
                        >
                            Tutup
                        </button>

                        <div className="flex gap-2">

                            <button
                                className="px-4 py-2 bg-cyan-500 text-white rounded"
                                onClick={() => openJamModal(jamData, jamType)}
                            >
                                Reset
                            </button>

                            <button
                                className="px-4 py-2 bg-blue-600 text-white rounded"
                                onClick={() => {
                                    router.post("/absensi/update-jam", {
                                        id: jamData.id,
                                        type: jamType,
                                        status: jamForm.status,
                                        jam: jamForm.jam,
                                        menit: jamForm.menit,
                                        notes: jamForm.notes
                                    }, {
                                        preserveScroll: true,
                                        onSuccess: () => setJamModal(false),
                                        onError: (err) => {
                                            console.log("VALIDATION ERROR", err)
                                        }

                                    })
                                }}
                            >
                                Simpan
                            </button>

                        </div>
                    </div>
                }
            >

                <div className="space-y-3">

                    <div className="font-semibold">
                        {jamData?.nama}
                    </div>

                    <div className="flex gap-2">

                        <select
                            className="border p-2 flex-1"
                            value={jamForm.status}
                            onChange={e => setJamForm({ ...jamForm, status: e.target.value })}
                        >
                            {statusIn.map(s => (
                                <option key={s.name} value={s.name}>
                                    {s.name} — {s.desc}
                                </option>
                            ))}
                        </select>

                        <input
                            className="border p-2 w-16 text-center"
                            value={jamForm.jam}
                            onChange={e => setJamForm({ ...jamForm, jam: e.target.value })}
                        />

                        <input
                            className="border p-2 w-16 text-center"
                            value={jamForm.menit}
                            onChange={e => setJamForm({ ...jamForm, menit: e.target.value })}
                        />

                    </div>

                    <textarea
                        className="border p-2 w-full"
                        placeholder="Komentar / alasan"
                        value={jamForm.notes}
                        onChange={e => setJamForm({ ...jamForm, notes: e.target.value })}
                    />

                    <div className="text-xs text-gray-400">
                        {jamData?.id} {filters.date} {jamType}
                    </div>

                </div>

            </Modal>




        </AdminLayout>
    );
}
