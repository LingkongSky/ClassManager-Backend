import React from 'react';
import beainImg from '../Resource/beian.png';

const Filing: React.FC = () => {
    const icpText = import.meta.env.VITE_FILING_ICP_TEXT || 'ICP备案号（示例）';
    const icpUrl = import.meta.env.VITE_FILING_ICP_URL || 'https://beian.miit.gov.cn/';
    const psbText = import.meta.env.VITE_FILING_PSB_TEXT || '公网安备号（示例）';
    const psbUrl = import.meta.env.VITE_FILING_PSB_URL || 'http://www.beian.gov.cn/';

    return (
        <div>
            <a href={icpUrl} target="_blank" rel="noreferrer" style={{ color: 'rgba(0, 0, 0, 0.88)' }}>
                {icpText}
            </a>
            <br />
            <a target="_blank" rel="noreferrer" href={psbUrl} style={{ color: 'rgba(0, 0, 0, 0.88)' }}>
                <img src={beainImg} alt="" />
                {psbText}
            </a>
        </div>
    );
};

export default Filing;
