import AdminLayout from '../../Layouts/AdminLayout'
import { useForm, router } from '@inertiajs/react'
import { useState } from 'react'

export default function Index({ units, filters }) {
  const [modal, setModal] = useState(false)
  const [edit, setEdit] = useState(null)

  const { data, setData, post, put, reset, errors } = useForm({
    unit: '',
  })

  function openCreate() {
    reset()
    setEdit(null)
    setModal(true)
  }

  function openEdit(u) {
    setEdit(u)
    setData({ unit: u.unit })
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
      put(`/unit/${edit.id}`, {
        onSuccess: () => closeModal(),
      })
    } else {
      post('/unit', {
        onSuccess: () => closeModal(),
      })
    }
  }

  return (
    <AdminLayout title="Unit">

      <div className="box">
        <div className="box-header">
          <button className="btn btn-primary" onClick={openCreate}>
            Tambah Unit
          </button>

          <div className="pull-right">
            <input
              className="form-control"
              placeholder="Search..."
              style={{ width: '200px' }}
              defaultValue={filters.search}
              onChange={e =>
                router.get('/unit', { search: e.target.value }, {
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
                <th>Unit</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {units.data.map((u, i) => (
                <tr key={u.id}>
                  <td>{i + 1}</td>
                  <td>{u.id}</td>
                  <td>{u.unit}</td>
                  <td>
                    <button
                      className="btn btn-xs btn-warning"
                      onClick={() => openEdit(u)}
                    >
                      Edit
                    </button>
                    <button
                      className="btn btn-xs btn-danger"
                      onClick={() => {
                        if (confirm('Yakin hapus unit ini?')) {
                          router.delete(`/unit/${u.id}`, {
                            preserveScroll: true,
                          })
                        }
                      }}
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          <div className="text-center">
            {units.links.map((link, i) => (
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

      {/* MODAL */}
      {modal && (
        <div className="modal fade in" style={{ display: 'block' }}>
          <div className="modal-dialog">
            <form className="modal-content" onSubmit={submit}>
              <div className="modal-header">
                <h4>{edit ? 'Edit' : 'Tambah'} Unit</h4>
              </div>

              <div className="modal-body">
                <input
                  className="form-control"
                  placeholder="Nama Unit"
                  value={data.unit}
                  onChange={e => setData('unit', e.target.value)}
                />
                {errors.unit && <div className="text-danger">{errors.unit}</div>}
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
    </AdminLayout>
  )
}
