const GRADIENTS = {
    primary: 'linear-gradient(135deg, #6777ef, #3c4fd6)',
    secondary: 'linear-gradient(135deg, #98a6ad, #6c757d)',
    success: 'linear-gradient(135deg, #47c363, #2f9e4f)',
    danger: 'linear-gradient(135deg, #fc544b, #d63031)',
    warning: 'linear-gradient(135deg, #ffa426, #f6820d)',
    info: 'linear-gradient(135deg, #3abaf4, #1d9bd7)',
};

export default function MenuTile({ menu }) {
    return (
        <div className="col-lg-3 col-md-6 col-sm-6 col-12 mb-4">
            <a href={menu.url} className="menu-tile" title={menu.nama}>
                <div className="menu-ico" style={{ background: GRADIENTS[menu.color] || GRADIENTS.primary }}>
                    <i className={`fas fa-${menu.icon}`}></i>
                </div>
                <div className="menu-tile-body">
                    <h4>{menu.nama}</h4>
                    <span>
                        Buka menu <i className="fas fa-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>
    );
}
