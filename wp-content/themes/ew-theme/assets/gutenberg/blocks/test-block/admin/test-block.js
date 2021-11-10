import styles from './test-block.module.scss?module';

export default function TestBlock({ attributes, className, setAttributes }) {
  return (
    <div className={styles.testBlock}>
      <code>This is new component TestBlock</code>
    </div>
  );
}
