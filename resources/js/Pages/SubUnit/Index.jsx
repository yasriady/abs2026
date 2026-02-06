import AdminLayout from '../../Layouts/AdminLayout'
import { useForm, router } from '@inertiajs/react'
import { useState } from 'react'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal'

export default function Index({ subUnits, units, filters }) {
  const [modal, setModal] = useState(false)
  const [edit, setEdit] = useState(null)

  // // DELETE MODAL STATE
  // const [deleteModal, setDeleteModal] = useState(false)
  // const [deleteItem, setDeleteItem] = useState(null)
  const [deleteSubUnit, setDeleteSubUnit] = useState(null)

  const { data, setData, post, put, reset, errors } = useForm({
    id: '',
    sub_unit: '',
    unit_id: '',
  })

  function openCreate() {
    reset()
    setEdit(null)
    setModal(true)
  }

  function openEdit(su) {
    setEdit(su)
    setData({
      id: su.id,
      sub_unit: su.sub_unit,
      unit_id: su.unit_id,
    })
    setModal(true)
  }

  function closeModal() {
    reset()
    setEdit(null)
    setModal(false)
  }

  function submit(e) {
    e.preventDefault()

    if (edit) {
      put(`/subunit/${edit.id}`, {
        onSuccess: () => closeModal(),
      })
    } else {
      post('/subunit', {
        onSuccess: () => closeModal(),
      })
    }
  }

  return (
    <AdminLayout title="SubUnit">

      <div className="box">
        <div className="box-header">
          <button className="btn btn-primary" onClick={openCreate}>
            Tambah SubUnit
          </button>

          <div className="pull-right">
            <input
              className="form-control"
              placeholder="Search..."
              style={{ width: '200px' }}
              defaultValue={filters.search}
              onChange={e =>
                router.get('/subunit', { search: e.target.value }, {
                  preserveState: true,
                  replace: true,
                })
              }
            />
          </div>
        </div>

        <div className="box-body table-responsive">
          <table className="table table-bordered table-striped">
            <thead>
              <tr>
                <th width="40">No</th>
                <th width="60">ID</th>
                <th>Sub Unit</th>
                <th>Unit</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {subUnits.data.map((s, i) => (
                <tr key={s.id}>
                  <td>{i + 1}</td>
                  <td>{s.id}</td>
                  <td>{s.sub_unit}</td>
                  <td>{s.unit?.unit}</td>
                  <td>
                    <button
                      className="btn btn-xs btn-warning"
                      onClick={() => openEdit(s)}
                    >
                      Edit
                    </button>
                    <button
                      className="btn btn-xs btn-danger"
                      onClick={() => setDeleteSubUnit(s)}
                    >
                      Hapus
                    </button>

                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          <div className="text-center">
            {subUnits.links.map((link, i) => (
              <button
                key={i}
                disabled={!link.url}
                className="btn btn-default btn-xs"
                onClick={() => router.get(link.url)}
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            ))}
          </div>
        </div>
      </div>

      {/* MODAL CREATE / EDIT */}
      {modal && (
        <div className="modal fade in" style={{ display: 'block' }}>
          <div className="modal-dialog">
            <form className="modal-content" onSubmit={submit}>
              <div className="modal-header">
                <h4>{edit ? 'Edit' : 'Tambah'} SubUnit</h4>
              </div>

              <div className="modal-body">
                {!edit && (
                  <input
                    className="form-control"
                    placeholder="ID (Manual)"
                    value={data.id}
                    onChange={e => setData('id', e.target.value)}
                  />
                )}

                <input
                  className="form-control"
                  placeholder="Nama SubUnit"
                  value={data.sub_unit}
                  onChange={e => setData('sub_unit', e.target.value)}
                />

                <select
                  className="form-control"
                  value={data.unit_id}
                  onChange={e => setData('unit_id', e.target.value)}
                >
                  <option value="">-- Pilih Unit --</option>
                  {units.map(u => (
                    <option key={u.id} value={u.id}>
                      {u.unit}
                    </option>
                  ))}
                </select>

                {errors.sub_unit && <div className="text-danger">{errors.sub_unit}</div>}
                {errors.unit_id && <div className="text-danger">{errors.unit_id}</div>}
              </div>

              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-default"
                  onClick={closeModal}
                >
                  Batal
                </button>
                <button className="btn btn-primary">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <ConfirmDeleteModal
        show={!!deleteSubUnit}
        title="Konfirmasi Hapus SubUnit"
        message={
          <p>
            Yakin menghapus Sub Unit:
            <strong> {deleteSubUnit?.sub_unit}</strong>?
          </p>
        }
        onCancel={() => setDeleteSubUnit(null)}
        onConfirm={() => {
          router.delete(`/subunit/${deleteSubUnit.id}`, {
            preserveScroll: true,
            onSuccess: () => setDeleteSubUnit(null),
          })
        }}
      />

    </AdminLayout>
  )
}
